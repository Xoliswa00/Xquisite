<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Company;

class CompanyPolicy
{
    /**
     * View company list
     */
    public function viewAny(User $user): bool
    {
        return $user->isPlatformOwner() || $user->isSystemAdmin();
    }

    /**
     * View specific company
     */
    public function view(User $user, Company $company): bool
    {
        // Platform owner who manages the company
        if ($user->isPlatformOwner() && $company->platform_owner_id === $user->id) {
            return true;
        }

        // Client users belonging to company
        if ($user->companies->contains($company->id)) {
            return true;
        }

        return false;
    }

    /**
     * Create company
     */
    public function create(User $user): bool
    {
        return $user->isPlatformOwner();
    }

    /**
     * Update company
     */
    public function update(User $user, Company $company): bool
    {
        return $user->isPlatformOwner()
            && $company->platform_owner_id === $user->id;
    }

    /**
     * Delete company
     */
    public function delete(User $user, Company $company): bool
    {
        return $user->isPlatformOwner()
            && $company->platform_owner_id === $user->id;
    }

    /**
     * Restore company
     */
    public function restore(User $user, Company $company): bool
    {
        return false;
    }

    /**
     * Force delete
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return false;
    }
}