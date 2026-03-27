<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Subscription;
use App\Models\Invoice;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\UserTool;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Credit top-up packages.
     */
    protected function getPackages()
    {
        $defaultPackages = [
            'lite' => [ 'name' => 'Lite Dash', 'credits' => '100', 'price' => '35' ],
            'standard' => [ 'name' => 'Creator Pack', 'credits' => '500', 'price' => '150' ],
            'pro' => [ 'name' => 'Agency Pro', 'credits' => '2500', 'price' => '650' ],
            'enterprise' => [ 'name' => 'Power Node', 'credits' => '10000', 'price' => '2250' ]
        ];
        $savedPackagesJson = \App\Models\Setting::get('marketplace_packages');
        $packages = is_string($savedPackagesJson) ? json_decode($savedPackagesJson, true) : ($savedPackagesJson ?: $defaultPackages);

        // Calculate final sale prices
        foreach ($packages as $k => $pkg) {
            $basePrice = (float)str_replace(',', '', $pkg['price']);
            $discount = isset($pkg['discount']) ? (float)$pkg['discount'] : 0;
            $salePrice = $discount > 0 ? $basePrice - ($basePrice * ($discount / 100)) : $basePrice;
            $packages[$k]['final_price'] = $salePrice;
            // Clean credits string just in case
            $packages[$k]['credits_num'] = (int)str_replace(',', '', $pkg['credits']);
        }

        return $packages;
    }

    /**
     * Show the payment / order review page.
     */
    public function index(Request $request)
    {
        $type = $request->query('type'); // 'tool' or 'package'
        $id = $request->query('id');
        $isNewAccount = $request->query('new_account') === '1';

        // For new account registration, check session
        if ($isNewAccount && !Auth::check()) {
            $pending = session('pending_registration');
            if (!$pending) {
                return redirect('/register')->with('error', 'Please register first.');
            }
        } elseif (!$isNewAccount && !Auth::check()) {
            return redirect('/login')->with('error', 'Please log in first.');
        }

        if ($type === 'tool') {
            $toolConfig = collect(config('tools.all_tools', []))->where('slug', $id)->first();
            if (!$toolConfig) {
                return redirect('/dashboard')->with('error', 'Invalid tool selection.');
            }
            $unlockPrice = (int) \App\Models\Setting::get("tool_unlock_price_{$id}", $toolConfig['unlock_price'] ?? 99);
            $item = [
                'name' => $toolConfig['name'],
                'tagline' => $toolConfig['tagline'] ?? $toolConfig['name'],
                'icon' => $toolConfig['icon'] ?? 'fa-cube',
                'color' => $toolConfig['color'] ?? 'var(--primary-cyan)',
                'price' => $unlockPrice,
                'credits' => (int) \App\Models\Setting::get("tool_bonus_credits_{$id}", $toolConfig['initial_bonus_credits'] ?? 10),
            ];
        } elseif ($type === 'package') {
            $packages = $this->getPackages();
            if (isset($packages[$id])) {
                $item = $packages[$id];
                $item['price'] = $item['final_price']; // Pass final price to view
                $item['credits'] = $item['credits_num'];
            } else {
                return redirect('/dashboard')->with('error', 'Invalid selection.');
            }
        } else {
            return redirect('/dashboard')->with('error', 'Invalid selection.');
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

    /**
     * Initiate payment via Fawaterk V2 createInvoiceLink.
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:tool,package',
            'id' => 'required',
        ]);

        $user = Auth::user();
        $type = $request->type;
        $id = $request->id;
        $pending = session('pending_registration');

        if (!$user && $pending) {
            $guestName = $pending['name'] ?? 'User VidaNexus';
            $guestEmail = $pending['email'] ?? 'guest@vidanexus.com';
        }

        if ($type === 'tool') {
            $toolConfig = collect(config('tools.all_tools', []))->where('slug', $id)->first();
            if (!$toolConfig) return back()->with('error', 'Invalid tool.');
            $unlockPrice = (int) \App\Models\Setting::get("tool_unlock_price_{$id}", $toolConfig['unlock_price'] ?? 99);
            $item = ['name' => $toolConfig['name'], 'price' => $unlockPrice];
        } elseif ($type === 'package') {
            $packages = $this->getPackages();
            if (isset($packages[$id])) {
                $item = $packages[$id];
                $item['price'] = $item['final_price']; // Force final price for Fawaterk
            } else {
                return back()->with('error', 'Invalid selection.');
            }
        } else {
            return back()->with('error', 'Invalid selection.');
        }

        $apiKey = config('services.fawaterk.api_key', env('FAWATERK_API_KEY'));
        $userId = $user ? $user->id : 'NEW';
        $orderRef = 'VN_' . strtoupper($type) . '_' . $id . '_' . $userId . '_' . time();

        $fullName = $user ? $user->name : ($guestName ?? 'User VidaNexus');
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?: 'User';
        $lastName = $nameParts[1] ?? 'VidaNexus';
        $email = $user ? $user->email : ($guestEmail ?? 'guest@vidanexus.com');
        $phone = $user ? ($user->phone ?? '01000000000') : '01000000000';
        $address = $user ? ($user->country ?? 'Online Platform') : 'Online Platform';

        $newAccountFlag = $pending ? '&new_account=1' : '';

        $payload = [
            'cartTotal' => (string) $item['price'],
            'currency' => 'EGP',
            'customer' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
            ],
            'redirectionUrls' => [
                'successUrl' => url('/payment/fawaterk/callback?status=success&type=' . $type . '&id=' . $id . '&ref=' . $orderRef . $newAccountFlag),
                'failUrl' => url('/payment/fawaterk/callback?status=fail&ref=' . $orderRef . $newAccountFlag),
                'pendingUrl' => url('/payment/fawaterk/callback?status=pending&ref=' . $orderRef . $newAccountFlag),
            ],
            'cartItems' => [
                [
                    'name' => 'VidaNexus ' . str_replace('—', '-', $item['name']) . ($type === 'tool' ? ' - Monthly Subscription' : ' Package'),
                    'price' => (string) $item['price'],
                    'quantity' => '1',
                ]
            ],
            'pay_load' => $orderRef,
        ];

        try {
            $response = Http::timeout(60)
                ->withToken($apiKey)
                ->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                    ]
                ])
                ->post('https://app.fawaterk.com/api/v2/createInvoiceLink', $payload);

            $data = $response->json();

            Log::info('Fawaterk createInvoiceLink Response', [
                'status_code' => $response->status(),
                'response' => $data,
                'order_ref' => $orderRef,
                'user_id' => $user?->id ?? 'NEW',
            ]);

            if ($response->successful() && isset($data['data']['url'])) {
                return redirect($data['data']['url']);
            }

            if (isset($data['status']) && $data['status'] === 'success' && isset($data['data']['url'])) {
                return redirect($data['data']['url']);
            }

            $errorMsg = 'Could not initiate payment.';
            if (isset($data['message'])) {
                $errorMsg .= ' ' . $data['message'];
            }

            Log::error('Fawaterk API Error', ['response' => $data, 'payload' => $payload]);
            return back()->with('error', $errorMsg);

        } catch (\Exception $e) {
            Log::error('Fawaterk Exception', [
                'msg' => $e->getMessage(),
                'server_ip' => '135.125.190.148',
                'api_endpoint' => 'https://app.fawaterk.com/api/v2/createInvoiceLink'
            ]);
            return back()->with('error', 'Payment service is temporarily unavailable. Please try again later.');
        }
    }

    /**
     * Handle Fawaterk callback after payment.
     */
    public function callback(Request $request)
    {
        $status = $request->query('status');
        $type = $request->query('type');
        $id = $request->query('id');
        $ref = $request->query('ref');
        $isNewAccount = $request->query('new_account') === '1';

        $user = Auth::user();
        $pending = session('pending_registration');

        Log::info('Fawaterk Callback Received', [
            'status' => $status, 'type' => $type, 'id' => $id, 'ref' => $ref,
            'user_id' => $user ? $user->id : 'NEW',
        ]);

        // Handle failed / pending payments
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

        // ── Success flow ──

        // If new account registration, create user now
        if ($isNewAccount && $pending && !$user) {
            $user = \App\Models\User::create([
                'name' => $pending['name'],
                'email' => $pending['email'],
                'password' => $pending['password'],
                'role' => 'user',
                'subscription_tier' => 'beginner',
            ]);

            Wallet::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'balance_credits' => 0.00,
            ]);

            Auth::login($user);
            session()->forget('pending_registration');
        }

        if (!$user) {
            return redirect('/register')->with('error', 'An error occurred. Please register again.');
        }

        // Prevent duplicate processing
        $existingInvoice = Invoice::where('description', 'LIKE', '%' . ($ref ?? 'NONE') . '%')->first();
        if ($existingInvoice) {
            return redirect('/dashboard#billing')->with('success', 'This order has already been processed.');
        }

        if ($type === 'tool') {
            return $this->finalizeToolPurchase($user, $id, $ref);
        } elseif ($type === 'package') {
            $packages = $this->getPackages();
            if (isset($packages[$id])) {
                return $this->finalizeCreditPurchase($user, $id, $ref, $packages[$id]);
            }
        }

        return redirect('/dashboard')->with('error', 'Could not finalize your order. Please contact support.');
    }

    /**
     * Finalize a tool subscription (1 month access + bonus credits).
     */
    private function finalizeToolPurchase($user, $toolSlug, $ref = null)
    {
        $toolConfig = collect(config('tools.all_tools', []))->where('slug', $toolSlug)->first();
        if (!$toolConfig) {
            return redirect('/dashboard')->with('error', 'Tool not found.');
        }

        // Check if already purchased
        if (UserTool::where('user_id', $user->id)->where('tool_slug', $toolSlug)->exists()) {
            return redirect('/dashboard')->with('success', 'You already own this tool!');
        }

        $unlockPrice = (int) \App\Models\Setting::get("tool_unlock_price_{$toolSlug}", $toolConfig['unlock_price'] ?? 99);
        $bonusCredits = (int) \App\Models\Setting::get("tool_bonus_credits_{$toolSlug}", $toolConfig['initial_bonus_credits'] ?? 10);

        // 1. Record tool subscription (valid for 1 month)
        UserTool::create([
            'user_id' => $user->id,
            'tool_slug' => $toolSlug,
            'price_paid' => $unlockPrice,
            'bonus_credits' => $bonusCredits,
            'expires_at' => now()->addMonth(),
            'renews_at' => now()->addMonth(),
            'auto_renew' => true,
        ]);

        // 2. Create invoice
        Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => null,
            'amount' => $unlockPrice,
            'credits_granted' => $bonusCredits,
            'status' => 'paid',
            'description' => 'Tool Subscription: ' . $toolConfig['name'] . ' [' . ($ref ?? 'N/A') . ']',
            'paid_at' => now(),
        ]);

        // 3. Credit bonus is kept in UserTool only (not global wallet)
        // Bonus credits are now subtracted in User::deductToolCredits before wallet fallback.
        Log::info('Tool Unlocked with Bonus Credits', [
            'user_id' => $user->id,
            'tool' => $toolSlug,
            'bonus' => $bonusCredits
        ]);

        return redirect('/dashboard')->with('success', '🎉 Successfully subscribed to "' . $toolConfig['name'] . '" for 1 month! ' . $bonusCredits . ' bonus CRS have been added to your wallet.');
    }

    /**
     * Finalize a credit package purchase after successful payment.
     */
    private function finalizeCreditPurchase($user, $packageId, $ref = null, $packageInfo = null)
    {
        if (!$packageInfo) {
            $packages = $this->getPackages();
            $packageInfo = $packages[$packageId] ?? null;
        }

        if (!$packageInfo) return redirect('/dashboard')->with('error', 'Invalid package.');

        Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => null,
            'amount' => $packageInfo['final_price'],
            'credits_granted' => $packageInfo['credits_num'],
            'status' => 'paid',
            'description' => 'Fawaterk: ' . $packageInfo['name'] . ' [' . ($ref ?? 'N/A') . ']',
            'paid_at' => now(),
        ]);

        if ($user->wallet) {
            $user->wallet->balance_credits += $packageInfo['credits_num'];
            $user->wallet->save();

            Transaction::create([
                'id' => (string) Str::uuid(),
                'wallet_id' => $user->wallet->id,
                'type' => 'deposit',
                'amount' => $packageInfo['credits_num'],
                'tool_name' => 'Credit Pack: ' . $packageInfo['name'],
                'idempotency_key' => 'FT_TOPUP_' . ($ref ?? uniqid()) . '_' . time(),
            ]);
        }

        return redirect('/dashboard#billing')->with('success', '🎉 Successfully purchased ' . number_format($packageInfo['credits_num']) . ' CRS! Credits added to your wallet.');
    }
}
