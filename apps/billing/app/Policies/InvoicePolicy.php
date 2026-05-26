<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Invoice;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->currentCompany?->id === $invoice->company_id;
    }

    public function create(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->currentCompany?->id === $invoice->company_id
            && $invoice->status !== 'paid';
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->currentCompany?->id === $invoice->company_id;
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->currentCompany?->id === $invoice->company_id;
    }

    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->currentCompany?->id === $invoice->company_id;
    }

    // Used by PaymentController::store()
    public function pay(User $user, Invoice $invoice): bool
    {
        return $user->currentCompany?->id === $invoice->company_id
            && $invoice->status !== 'paid';
    }
}
