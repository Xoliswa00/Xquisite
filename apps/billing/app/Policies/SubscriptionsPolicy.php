<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subscription;

class SubscriptionsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->currentCompany?->id === $subscription->company_id;
    }

    public function create(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->currentCompany?->id === $subscription->company_id
            && $subscription->status !== 'cancelled';
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->currentCompany?->id === $subscription->company_id;
    }

    public function restore(User $user, Subscription $subscription): bool
    {
        return $user->currentCompany?->id === $subscription->company_id;
    }

    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return $user->currentCompany?->id === $subscription->company_id;
    }
}
