<?php

namespace App\Services\Credits;

use App\Models\FinancialLedgerEntry;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserTool;
use App\Services\Logging\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Applies tool usage credit rules: system (wallet) credits first, then per-tool bonus only when allowed
 * (trial / subscription-style grants). Marketplace-paid tool rows do not consume bonus_credits for AI usage.
 */
class ToolCreditConsumptionService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function canUse(User $user, string $slug, ?int $explicitCost = null): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! $user->ownsTool($slug)) {
            return false;
        }

        $cost = $explicitCost !== null ? $explicitCost : $user->getToolCreditCost($slug);
        if ($cost <= 0) {
            return true;
        }

        $userTool = $user->relationLoaded('ownedTools')
            ? $user->ownedTools->firstWhere('tool_slug', $slug)
            : $user->ownedTools()->where('tool_slug', $slug)->first();

        $allowBonus = $userTool?->allow_bonus_for_ai_usage ?? true;

        $wallet = (int) ($user->wallet->balance_credits ?? 0);
        if (! $allowBonus) {
            return $wallet >= $cost;
        }

        $bonus = (int) ($userTool->bonus_credits ?? 0);

        return ($wallet + $bonus) >= $cost;
    }

    /**
     * Deduct credits for one tool action. Creates wallet transactions (withdrawal) and ledger rows.
     */
    public function deduct(User $user, string $slug, ?int $explicitCost = null): bool
    {
        $cost = $explicitCost !== null ? $explicitCost : $user->getToolCreditCost($slug);
        if ($cost <= 0) {
            return true;
        }

        $toolName = $this->resolveToolDisplayName($slug);

        try {
            return DB::transaction(function () use ($user, $slug, $cost, $toolName) {
                $userTool = UserTool::query()
                    ->where('user_id', $user->id)
                    ->where('tool_slug', $slug)
                    ->lockForUpdate()
                    ->first();

                $wallet = $user->wallet()->lockForUpdate()->first();
                if (! $wallet) {
                    return false;
                }

                $allowBonus = $userTool?->allow_bonus_for_ai_usage ?? true;

                $walletBal = (int) $wallet->balance_credits;
                $bonusBal = ($allowBonus && $userTool) ? (int) $userTool->bonus_credits : 0;

                if (($walletBal + $bonusBal) < $cost) {
                    return false;
                }

                $remaining = $cost;
                $fromWallet = 0;
                $fromBonus = 0;

                if ($walletBal > 0 && $remaining > 0) {
                    $take = min($walletBal, $remaining);
                    $wallet->balance_credits = $walletBal - $take;
                    $wallet->save();
                    $fromWallet = $take;
                    $remaining -= $take;
                }

                if ($remaining > 0 && $allowBonus && $userTool && $userTool->bonus_credits > 0) {
                    $b = (int) $userTool->bonus_credits;
                    $take = min($b, $remaining);
                    $userTool->bonus_credits = $b - $take;
                    $userTool->save();
                    $fromBonus = $take;
                    $remaining -= $take;
                }

                if ($remaining > 0) {
                    Log::critical('Tool credit deduction inconsistency', [
                        'user_id' => $user->id,
                        'slug' => $slug,
                        'remaining' => $remaining,
                    ]);

                    return false;
                }

                if ($fromWallet > 0) {
                    Transaction::create([
                        'id' => (string) Str::uuid(),
                        'wallet_id' => $wallet->id,
                        'type' => 'withdrawal',
                        'amount' => $fromWallet,
                        'tool_name' => $toolName,
                        'idempotency_key' => 'USE_WALLET_'.$slug.'_'.$user->id.'_'.Str::uuid(),
                    ]);
                }

                FinancialLedgerEntry::create([
                    'user_id' => $user->id,
                    'event_type' => 'tool_ai_usage',
                    'wallet_delta' => -$fromWallet,
                    'bonus_delta' => -$fromBonus,
                    'tool_slug' => $slug,
                    'reference' => null,
                    'meta' => [
                        'tool_name' => $toolName,
                        'cost' => $cost,
                        'from_wallet' => $fromWallet,
                        'from_bonus' => $fromBonus,
                    ],
                ]);

                $this->auditLogService->log(
                    $user->id,
                    'credits.consume',
                    'tool_usage',
                    $slug,
                    null,
                    [
                        'tool_name' => $toolName,
                        'cost' => $cost,
                        'from_wallet' => $fromWallet,
                        'from_bonus' => $fromBonus,
                    ]
                );

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('ToolCreditConsumptionService::deduct failed: '.$e->getMessage(), [
                'user_id' => $user->id,
                'slug' => $slug,
            ]);

            return false;
        }
    }

    private function resolveToolDisplayName(string $slug): string
    {
        $tools = config('tools.all_tools', []);
        $tool = collect($tools)->firstWhere('slug', $slug);

        return is_array($tool) ? ($tool['name'] ?? $slug) : $slug;
    }
}
