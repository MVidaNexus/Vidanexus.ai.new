<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\InitiatePaymentRequest;
use App\Http\Requests\Billing\ShowPaymentPageRequest;
use App\Jobs\ProcessPaymentEventJob;
use App\Repositories\Payments\PaymentIntentRepository;
use App\Services\Billing\FawaterkInvoiceService;
use App\Services\Billing\PaymentCatalogService;
use App\Services\Billing\PaymentFulfillmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentCatalogService $catalog,
        protected FawaterkInvoiceService $fawaterk,
        protected PaymentFulfillmentService $fulfillment,
        protected PaymentIntentRepository $paymentIntentRepository
    ) {}

    public function index(ShowPaymentPageRequest $request): RedirectResponse|View
    {
        $type = $request->validated('type');
        $id = $request->validated('id');
        $isNewAccount = $request->query('new_account') === '1';
        $pending = null;

        if ($isNewAccount && ! Auth::check()) {
            $pending = session('pending_registration');
            if (! $pending) {
                return redirect('/register')->with('error', 'Please register first.');
            }
        } elseif (! $isNewAccount && ! Auth::check()) {
            return redirect('/login')->with('error', 'Please log in first.');
        }

        if ($type === 'tool') {
            $item = $this->catalog->resolveToolDisplayItem($id);
            if (! $item) {
                return redirect('/dashboard')->with('error', 'Invalid tool selection.');
            }
        } else {
            $packages = $this->catalog->getPackages();
            if (! isset($packages[$id])) {
                return redirect('/dashboard')->with('error', 'Invalid selection.');
            }
            $item = $packages[$id];
            $item['price'] = $item['final_price'];
            $item['credits'] = $item['credits_num'];
        }

        return view('payment', [
            'type' => $type,
            'id' => $id,
            'item' => $item,
            'user' => Auth::user(),
            'isNewAccount' => $isNewAccount,
            'pendingName' => $pending['name'] ?? null,
        ]);
    }

    public function initiate(InitiatePaymentRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $type = $request->validated('type');
        $id = $request->validated('id');
        $pending = session('pending_registration');

        $guestName = null;
        $guestEmail = null;
        if (! $user && $pending) {
            $guestName = $pending['name'] ?? 'User VidaNexus';
            $guestEmail = $pending['email'] ?? 'guest@vidanexus.com';
        }

        if ($type === 'tool') {
            $item = $this->catalog->resolveToolCheckoutItem($id);
            if (! $item) {
                return back()->with('error', 'Invalid tool.');
            }
        } else {
            $packages = $this->catalog->getPackages();
            if (! isset($packages[$id])) {
                return back()->with('error', 'Invalid selection.');
            }
            $item = $packages[$id];
            $item['price'] = $item['final_price'];
        }

        $orderRef = 'VN_'.strtoupper($type).'_'.$id.'_'.($user?->id ?? 'NEW').'_'.Str::upper(Str::random(8));
        $idempotencyKey = hash('sha256', implode('|', [$user?->id ?? 'new', $type, $id, $orderRef]));

        $intent = $this->paymentIntentRepository->createOrGet(
            ['idempotency_key' => $idempotencyKey],
            [
                'user_id' => $user?->id,
                'provider' => 'fawaterk',
                'provider_order_ref' => $orderRef,
                'payment_type' => $type,
                'payment_target_id' => $id,
                'amount_egp' => (int) round((float) $item['price']),
                'state' => 'pending',
                'meta' => ['new_account' => (bool) $pending],
            ]
        );

        $result = $this->fawaterk->createInvoiceLink(
            $user,
            $type,
            $id,
            $item,
            $intent->provider_order_ref,
            $guestName,
            $guestEmail,
            (bool) $pending
        );

        if ($result['url']) {
            return redirect($result['url']);
        }

        return back()->with('error', $result['error'] ?? 'Could not initiate payment.');
    }

    public function callback(Request $request): RedirectResponse
    {
        $status = $request->query('status');
        $ref = $request->query('ref');
        $isNewAccount = $request->query('new_account') === '1';

        $user = Auth::user();
        $pending = session('pending_registration');

        Log::info('Fawaterk Callback Received', [
            'status' => $status, 'ref' => $ref,
            'user_id' => $user ? $user->id : 'NEW',
        ]);

        $intent = $this->paymentIntentRepository->findByOrderRef((string) $ref);
        if ($intent && in_array($status, ['fail', 'pending'], true)) {
            $intent->update([
                'state' => $status === 'fail' ? 'failed' : 'pending',
                'last_event_at' => now(),
                'failure_reason' => $status === 'fail' ? 'Provider callback failed.' : null,
            ]);
        }

        if ($status === 'fail') {
            if ($isNewAccount && $pending) {
                return redirect('/register')->with('error', 'Payment failed. Account was not created. Please try again.');
            }

            return redirect('/dashboard#billing')->with('error', 'Payment failed. No charges were made. Please try again.');
        }

        if ($status === 'pending') {
            return redirect('/dashboard#billing')->with('error', 'Payment is still pending. Your credits will be added once payment is confirmed.');
        }

        if ($status !== 'success') {
            return redirect('/dashboard#billing')->with('error', 'Payment was cancelled or not completed.');
        }

        if ($isNewAccount && $pending && ! $user) {
            return redirect('/register')->with('success', 'Payment confirmed. We are finalizing your account shortly.');
        }

        return redirect('/dashboard#billing')->with('success', 'Payment confirmed. Finalization is in progress.');
    }

    public function webhook(Request $request): JsonResponse
    {
        if (! $this->verifyWebhookSignature($request)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $payload = $request->all();
        $orderRef = (string) ($payload['order_ref'] ?? $payload['payLoad'] ?? $payload['pay_load'] ?? '');
        $status = (string) ($payload['status'] ?? 'pending');
        $eventId = (string) ($payload['event_id'] ?? hash('sha256', json_encode($payload).microtime(true)));

        $intent = $this->paymentIntentRepository->findByOrderRef($orderRef);
        if (! $intent) {
            return response()->json(['message' => 'Unknown order reference'], 404);
        }

        $event = $this->paymentIntentRepository->recordEvent($intent, [
            'provider_event_id' => $eventId,
            'provider' => 'fawaterk',
            'provider_order_ref' => $orderRef,
            'provider_status' => $status,
            'signature_valid' => true,
            'payload' => $payload,
        ]);

        ProcessPaymentEventJob::dispatch($event->id);

        return response()->json(['message' => 'Accepted'], 202);
    }

    protected function verifyWebhookSignature(Request $request): bool
    {
        $headerSignature = (string) $request->header('X-Fawaterk-Signature', '');
        $secret = (string) config('services.fawaterk.webhook_secret', env('FAWATERK_WEBHOOK_SECRET', ''));
        if ($secret === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $headerSignature);
    }
}
