<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function create(User $user): bool
    {
        return $user->can('manage_payments') || $user->isAdmin();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->id === $invoice->user_id || $user->can('manage_payments') || $user->isAdmin();
    }
}
