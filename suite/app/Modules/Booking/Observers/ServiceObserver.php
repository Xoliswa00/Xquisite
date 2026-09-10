<?php

namespace App\Modules\Booking\Observers;

use App\Modules\Booking\Models\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ServiceObserver
{
    public function saved(Service $service): void
    {
        $this->bustPortalCache($service);
    }

    public function deleted(Service $service): void
    {
        $this->bustPortalCache($service);
    }

    public function restored(Service $service): void
    {
        $this->bustPortalCache($service);
    }

    public function forceDeleted(Service $service): void
    {
        // Photo rows cascade via the FK; drop the whole folder so the files don't orphan on disk.
        Storage::disk('public')->deleteDirectory("services/{$service->id}");
        $this->bustPortalCache($service);
    }

    private function bustPortalCache(Service $service): void
    {
        if ($service->tenant_id) {
            Cache::forget("book:index:services:{$service->tenant_id}");
        }
    }
}
