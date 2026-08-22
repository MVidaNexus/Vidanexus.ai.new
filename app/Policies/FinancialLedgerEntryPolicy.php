<?php

namespace App\Policies;

use App\Models\FinancialLedgerEntry;
use App\Models\User;

class FinancialLedgerEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_ledger') || $user->isAdmin();
    }

    public function view(User $user, FinancialLedgerEntry $financialLedgerEntry): bool
    {
        return $this->viewAny($user);
    }
}
