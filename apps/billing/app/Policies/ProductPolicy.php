<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Product;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function view(User $user, Product $product): bool
    {
        return $user->currentCompany?->id === $product->company_id;
    }

    public function create(User $user): bool
    {
        return $user->currentCompany !== null;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->currentCompany?->id === $product->company_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->currentCompany?->id === $product->company_id;
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->currentCompany?->id === $product->company_id;
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->currentCompany?->id === $product->company_id;
    }
}
