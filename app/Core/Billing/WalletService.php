<?php

namespace App\Core\Billing;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    /**
     * Calculate credit cost based on tokens and markup.
     */
    public function calculateCreditCost(string $provider, string $model, int $inputTokens, int $outputTokens): float
    {
        $rates = config("vidanexus.ai.rates.{$provider}.{$model}");
        $markup = config("vidanexus.ai.markup", 1.4);

        if (!$rates) {
            // Fallback to a safe default if model not found
            $rates = ['input' => 0.00001, 'output' => 0.00003];
        }

        $baseCost = ($inputTokens * $rates['input']) + ($outputTokens * $rates['output']);
        
        return $baseCost * $markup;
    }

    /**
     * Lock credits for an upcoming AI task.
     */
    public function lockCredits(User $user, float $amount, string $tool, string $idempotencyKey): string
    {
        return DB::transaction(function () use ($user, $amount, $tool, $idempotencyKey) {
            $wallet = $user->wallet()->lockForUpdate()->first();

            if (!$wallet || $wallet->balance_credits < $amount) {
                throw new \Exception("Insufficient credits.");
            }

            $wallet->decrement('balance_credits', $amount);

            $transactionId = (string) Str::uuid();
            DB::table('transactions')->insert([
                'id' => $transactionId,
                'wallet_id' => $wallet->id,
                'type' => 'lock',
                'amount' => $amount,
                'tool_name' => $tool,
                'idempotency_key' => $idempotencyKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $transactionId;
        });
    }

    /**
     * Settle actual usage and refund excess.
     */
    public function settleCredits(string $lockTransactionId, float $actualAmount): void
    {
        DB::transaction(function () use ($lockTransactionId, $actualAmount) {
            $lock = DB::table('transactions')->where('id', $lockTransactionId)->where('type', 'lock')->first();
            
            if (!$lock) {
                throw new \Exception("Invalid or non-existent lock transaction.");
            }

            $refund = $lock->amount - $actualAmount;

            // Mark lock as settled by updating its status or creating a reference
            DB::table('transactions')->where('id', $lockTransactionId)->update(['type' => 'withdrawal']);

            if ($refund > 0) {
                DB::table('wallets')->where('id', $lock->wallet_id)->increment('balance_credits', $refund);

                DB::table('transactions')->insert([
                    'id' => (string) Str::uuid(),
                    'wallet_id' => $lock->wallet_id,
                    'type' => 'refund',
                    'amount' => $refund,
                    'reference_id' => $lockTransactionId,
                    'idempotency_key' => 'refund_' . $lockTransactionId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
