<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Only tenant owners and admins can manage staff.
     */
    public function manageStaff(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Owners can view any user in their tenant.
     */
    public function view(User $viewer, User $user): bool
    {
        return ($viewer->isAdmin() && $viewer->tenant_id === $user->tenant_id) ||
               $viewer->id === $user->id;
    }

    /**
     * Only admins can update users in their tenant.
     */
    public function update(User $editor, User $user): bool
    {
        return $editor->isAdmin() && 
               $editor->tenant_id === $user->tenant_id &&
               $user->id !== $editor->id;
    }

    /**
     * Only admins can delete staff.
     */
    public function delete(User $editor, User $user): bool
    {
        return $editor->isAdmin() && 
               $editor->tenant_id === $user->tenant_id &&
               !$user->isOwner();
    }
}
