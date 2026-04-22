<?php

namespace App\Modules\Booking\Observers;

use App\Models\Staff;

class StaffObserver
{
    /**
     * Handle the Staff "created" event.
     */
    public function created(Staff $staff): void
    {
        //
    }

    /**
     * Handle the Staff "updated" event.
     */
    public function updated(Staff $staff): void
    {
        //
    }

    /**
     * Handle the Staff "deleted" event.
     */
    public function deleted(Staff $staff): void
    {
        //
    }

    /**
     * Handle the Staff "restored" event.
     */
    public function restored(Staff $staff): void
    {
        //
    }

    /**
     * Handle the Staff "force deleted" event.
     */
    public function forceDeleted(Staff $staff): void
    {
        //
    }
}
