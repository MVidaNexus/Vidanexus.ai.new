<?php

namespace App\Services\Billing;

use App\Models\FinancialLedgerEntry;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserTool;
use App\Models\Wallet;
use App\Services\Logging\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentFulfillmentService
{
    public function __construct(
        protected PaymentCatalogService $catalog,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Register user from pending_registration session after successful payment.
     */
    public function createUserFromPending(array $pending): User
    {
        $user = User::create([
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
        $this->auditLogService->log($user->id, 'user.create_paid_signup', User::class, $user->id, null, $user->toArray());

        return $user;
    }

    public function finalizeToolPurchase(User $user, string $toolSlug, ?string $ref): RedirectResponse
    {
        $toolConfig = collect(config('tools.all_tools', []))->where('slug', $toolSlug)->first();
        if (! $toolConfig) {
            return redirect('/dashboard')->with('error', 'Tool not found.');
        }

        if (UserTool::where('user_id', $user->id)->where('tool_slug', $toolSlug)->exists()) {
            return redirect('/dashboard')->with('success', 'You already own this tool!');
        }

        if ($ref && Invoice::query()->where('description', 'LIKE', '%'.$ref.'%')->exists()) {
            return redirect('/dashboard')->with('success', 'This order has already been processed.');
        }

        $unlockPrice = (int) Setting::get("tool_unlock_price_{$toolSlug}", $toolConfig['unlock_price'] ?? 99);
        $bonusCredits = (int) Setting::get("tool_bonus_credits_{$toolSlug}", $toolConfig['initial_bonus_credits'] ?? 10);

        UserTool::create([
            'user_id' => $user->id,
            'tool_slug' => $toolSlug,
            'price_paid' => $unlockPrice,
            'bonus_credits' => $bonusCredits,
            'allow_bonus_for_ai_usage' => false,
            'expires_at' => now()->addMonth(),
            'renews_at' => now()->addMonth(),
            'auto_renew' => true,
        ]);

        Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => null,
            'amount' => $unlockPrice,
            'credits_granted' => $bonusCredits,
            'status' => 'paid',
            'description' => 'Tool Subscription: '.$toolConfig['name'].' ['.($ref ?? 'N/A').']',
            'paid_at' => now(),
        ]);

        Log::info('Tool Unlocked with Bonus Credits', [
            'user_id' => $user->id,
            'tool' => $toolSlug,
            'bonus' => $bonusCredits,
        ]);
        $this->auditLogService->log(
            $user->id,
            'payment.tool_purchase_completed',
            UserTool::class,
            $toolSlug,
            null,
            ['tool_slug' => $toolSlug, 'ref' => $ref, 'unlock_price' => $unlockPrice, 'bonus_credits' => $bonusCredits]
        );

        return redirect('/dashboard')->with('success', '🎉 Successfully subscribed to "'.$toolConfig['name'].'" for 1 month! '.$bonusCredits.' bonus CRS have been added to your wallet.');
    }

    public function finalizeCreditPurchase(User $user, string $packageId, ?string $ref, ?array $packageInfo = null): RedirectResponse
    {
        if (! $packageInfo) {
            $packages = $this->catalog->getPackages();
            $packageInfo = $packages[$packageId] ?? null;
        }

        if (! $packageInfo) {
            return redirect('/dashboard')->with('error', 'Invalid package.');
        }

        if ($ref && Invoice::query()->where('description', 'LIKE', '%'.$ref.'%')->exists()) {
            return redirect('/dashboard#billing')->with('success', 'This order has already been processed.');
        }

        Invoice::create([
            'user_id' => $user->id,
            'subscription_id' => null,
            'amount' => $packageInfo['final_price'],
            'credits_granted' => $packageInfo['credits_num'],
            'status' => 'paid',
            'description' => 'Fawaterk: '.$packageInfo['name'].' ['.($ref ?? 'N/A').']',
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
                'tool_name' => 'Credit Pack: '.$packageInfo['name'],
                'idempotency_key' => 'PAYMENT_FAWATERK_'.($ref ?? ('pkg_'.$packageId.'_'.$user->id)),
            ]);

            FinancialLedgerEntry::create([
                'user_id' => $user->id,
                'event_type' => 'credit_pack_purchase',
                'wallet_delta' => (int) $packageInfo['credits_num'],
                'bonus_delta' => 0,
                'reference' => $ref,
                'meta' => ['package' => $packageId, 'name' => $packageInfo['name'] ?? null],
            ]);
            $this->auditLogService->log(
                $user->id,
                'payment.credit_purchase_completed',
                Wallet::class,
                $user->wallet->id,
                null,
                ['package_id' => $packageId, 'credits_num' => $packageInfo['credits_num'], 'ref' => $ref]
            );
        }

        return redirect('/dashboard#billing')->with('success', '🎉 Successfully purchased '.number_format($packageInfo['credits_num']).' CRS! Credits added to your wallet.');
    }
}
