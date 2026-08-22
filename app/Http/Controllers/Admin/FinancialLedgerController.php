<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialLedgerEntry;
use App\Repositories\Ledger\LedgerRepository;
use Illuminate\View\View;

/**
 * Admin read-only view of unified credit / bonus ledger events.
 */
class FinancialLedgerController extends Controller
{
    public function __construct(
        protected LedgerRepository $ledgerRepository
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', FinancialLedgerEntry::class);

        $entries = $this->ledgerRepository->paginateRecent(75);

        return view('admin.horizon.ledger', [
            'entries' => $entries,
        ]);
    }
}
