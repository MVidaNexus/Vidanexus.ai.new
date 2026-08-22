<?php

namespace App\Repositories\Ledger;

use App\Models\FinancialLedgerEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LedgerRepository
{
    public function paginateRecent(int $perPage = 75): LengthAwarePaginator
    {
        return FinancialLedgerEntry::query()
            ->with(['user:id,name,email'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function missingLedgerTransactionIds(): array
    {
        return DB::table('transactions as t')
            ->leftJoin('financial_ledger_entries as l', function ($join): void {
                $join->on('l.user_id', '=', 't.wallet_id');
            })
            ->whereNull('l.id')
            ->limit(200)
            ->pluck('t.id')
            ->toArray();
    }
}
