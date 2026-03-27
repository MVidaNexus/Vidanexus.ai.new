<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RenewToolSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tools:renew';
    protected $description = 'Automatically renew tool subscriptions by deducting credits from user wallets.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting tool subscription renewals...');

        // Find subscriptions that expire today or have already expired, and have auto_renew enabled
        $expiringTools = \App\Models\UserTool::where('auto_renew', true)
            ->where(function ($query) {
                $query->where('expires_at', '<=', now()->addHours(1))
                      ->orWhereNull('expires_at'); // Handle legacy tools
            })
            ->get();

        $this->info("Found {$expiringTools->count()} potential renewals.");

        foreach ($expiringTools as $userTool) {
            $user = $userTool->user;
            if (!$user) continue;

            $toolSlug = $userTool->tool_slug;
            $toolConfig = collect(config('tools.all_tools', []))->where('slug', $toolSlug)->first();
            if (!$toolConfig) {
                $this->warn("Tool config not found for {$toolSlug}. Skipping.");
                continue;
            }

            // Get the current renewal price (treating unlock_price as monthly price)
            $renewalPrice = (int) \App\Models\Setting::get("tool_unlock_price_{$toolSlug}", $toolConfig['unlock_price'] ?? 99);
            $bonusCredits = (int) \App\Models\Setting::get("tool_bonus_credits_{$toolSlug}", $toolConfig['initial_bonus_credits'] ?? 10);

            // Check if user has enough credits in global wallet
            if ($user->wallet && $user->wallet->balance_credits >= $renewalPrice) {
                $this->info("Renewing {$toolSlug} for user {$user->email}...");

                // 1. Deduct credits
                $user->wallet->balance_credits -= $renewalPrice;
                $user->wallet->save();

                // 2. Add bonus credits to tool balance
                $userTool->bonus_credits += $bonusCredits;
                
                // 3. Extend expiration
                $newExpiry = ($userTool->expires_at && $userTool->expires_at->isFuture()) 
                    ? $userTool->expires_at->addMonth() 
                    : now()->addMonth();
                    
                $userTool->expires_at = $newExpiry;
                $userTool->renews_at = $newExpiry;
                $userTool->price_paid = $renewalPrice;
                $userTool->save();

                // 4. Log transactions and invoices
                \App\Models\Transaction::create([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'wallet_id' => $user->wallet->id,
                    'type' => 'deduction',
                    'amount' => $renewalPrice,
                    'tool_name' => 'Monthly Renewal: ' . $toolConfig['name'],
                    'idempotency_key' => 'RENEW_' . $toolSlug . '_' . $user->id . '_' . $newExpiry->format('Ym'),
                ]);

                \App\Models\Invoice::create([
                    'user_id' => $user->id,
                    'amount' => $renewalPrice,
                    'credits_granted' => $bonusCredits,
                    'status' => 'paid',
                    'description' => 'Automated Renewal: ' . $toolConfig['name'],
                    'paid_at' => now(),
                ]);

                $this->info("Successfully renewed {$toolSlug} for {$user->email}. New expiry: {$newExpiry}");
            } else {
                $this->warn("Insufficient credits to renew {$toolSlug} for user {$user->email}. Processing expiry.");
                // We don't actively 'disable' it, the ownsTool check will just return false once expires_at is in the past.
            }
        }

        $this->info('Tool subscription renewals completed.');
    }
}
