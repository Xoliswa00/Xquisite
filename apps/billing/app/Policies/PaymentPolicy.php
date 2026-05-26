<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payment;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->currentCompany?->id === $payment->company_id;
    }

    public function create(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->currentCompany?->id === $payment->company_id;
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->currentCompany?->id === $payment->company_id;
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->currentCompany?->id === $payment->company_id;
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->currentCompany?->id === $payment->company_id;
    }
}
