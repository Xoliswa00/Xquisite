<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AuditService;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        AuditService::log(
            action: 'user.created',
            entityType: 'User',
            entityId: $user->id,
            new: $user->toArray()
        );
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        AuditService::log(
            action: 'user.updated',
            entityType: 'User',
            entityId: $user->id,
            old: $user->getOriginal(),
            new: $user->getChanges()
        );
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        AuditService::log(
            action: 'user.deleted',
            entityType: 'User',
            entityId: $user->id,
            old: $user->toArray()
        );
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        AuditService::log(
            action: 'user.restored',
            entityType: 'User',
            entityId: $user->id,
            new: $user->toArray()
        );
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        AuditService::log(
            action: 'user.forceDeleted',
            entityType: 'User',
            entityId: $user->id,
            old: $user->toArray()
        );
    }
}
