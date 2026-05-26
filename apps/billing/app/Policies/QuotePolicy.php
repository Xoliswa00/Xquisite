<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quote;

class QuotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function view(User $user, Quote $quote): bool
    {
        return $user->currentCompany?->id === $quote->company_id;
    }

    public function create(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function update(User $user, Quote $quote): bool
    {
        return $user->currentCompany?->id === $quote->company_id;
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->currentCompany?->id === $quote->company_id;
    }

    public function restore(User $user, Quote $quote): bool
    {
        return $user->currentCompany?->id === $quote->company_id;
    }

    public function forceDelete(User $user, Quote $quote): bool
    {
        return $user->currentCompany?->id === $quote->company_id;
    }

    // Used by InvoiceController::createFromQuote()
    public function convert(User $user, Quote $quote): bool
    {
        return $user->currentCompany?->id === $quote->company_id
            && in_array($quote->status, ['approved', 'sent']);
    }
}
