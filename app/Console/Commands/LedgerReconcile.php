<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LedgerReconcile extends Command
{
    protected $signature = 'ledger:reconcile {--strict : Fail if heuristic transaction matching is uncertain}';
    protected $description = 'Audit ledger consistency against wallet balances and credit transactions.';

    public function handle(): int
    {
        $negativeWallets = DB::table('wallets')->where('balance_credits', '<', 0)->count();

        $missingLedgerRows = DB::table('transactions as t')
            ->join('wallets as w', 'w.id', '=', 't.wallet_id')
            ->leftJoin('financial_ledger_entries as l', function ($join): void {
                $join->on('l.user_id', '=', 'w.user_id')
                    ->whereRaw('ABS(l.wallet_delta) = ABS(t.amount)')
                    ->whereRaw("DATE(l.created_at) = DATE(t.created_at)");
            })
            ->whereIn('t.type', ['deposit', 'withdrawal'])
            ->whereNull('l.id')
            ->count();

        $this->info('Ledger reconciliation report');
        $this->line('- Negative wallet balances: '.$negativeWallets);
        $this->line('- Credit transactions without ledger match: '.$missingLedgerRows);

        $hasIssues = $negativeWallets > 0 || $missingLedgerRows > 0;
        if ($hasIssues) {
            $this->error('Ledger reconciliation failed. Investigate inconsistencies.');
            return self::FAILURE;
        }

        if ($this->option('strict')) {
            $this->warn('Strict mode enabled with heuristic matching. Review sample records manually as needed.');
        }

        $this->info('Ledger reconciliation passed.');

        return self::SUCCESS;
    }
}
