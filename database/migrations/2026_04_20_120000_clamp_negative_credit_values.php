<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1) Clamp negative credit-like settings to zero.
        $rows = Setting::query()->select(['id', 'key', 'value'])->get();

        foreach ($rows as $row) {
            $key = (string) $row->key;
            $isCreditKey =
                str_starts_with($key, 'plan_credits_') ||
                str_starts_with($key, 'tool_credit_cost_') ||
                str_starts_with($key, 'tool_bonus_credits_');

            if (! $isCreditKey) {
                continue;
            }

            $numeric = (int) $row->value;
            if ($numeric < 0) {
                DB::table('settings')
                    ->where('id', $row->id)
                    ->update([
                        'value' => '0',
                        'updated_at' => now(),
                    ]);
            }
        }

        // 2) Clamp negative tool bonus credits.
        DB::table('user_tools')
            ->where('bonus_credits', '<', 0)
            ->update([
                'bonus_credits' => 0,
                'updated_at' => now(),
            ]);

        // 3) Clamp negative wallet balances.
        DB::table('wallets')
            ->where('balance_credits', '<', 0)
            ->update([
                'balance_credits' => 0,
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * This is intentionally non-reversible because we cannot infer original negative values safely.
     */
    public function down(): void
    {
        // No-op
    }
};

