<?php

namespace App\Modules\Booking\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ObserverRegistrar
{
    /**
     * Auto-registers every Observers/*.php class in the module against its
     * matching Models/*.php class, by filename convention.
     *
     * Gotcha: this fires on EVERY class in the folder, with no awareness of
     * observers registered elsewhere (e.g. an explicit ::observe() call in
     * AppServiceProvider). If a model already has an explicit observer
     * registered by name, either:
     *   (a) don't also add a same-named Observer here for that model, or
     *   (b) make sure it's the SAME class as the explicit one (Laravel doesn't
     *       de-dupe ::observe() calls — the same class registered twice fires
     *       its hooks twice per event; two DIFFERENT classes for the same
     *       model both fire, with whatever divergent logic each contains).
     * This bit twice already: App\Observers\AppointmentObserver (current) vs.
     * a stale App\Modules\Booking\Observers\AppointmentObserver (removed) both
     * firing and duplicating AppointmentReminder rows, and Customer::observe()
     * being called explicitly for a class this scan already covers.
     */
    public static function register(string $modulePath, string $baseNamespace): void
    {
        $observerPath = $modulePath . '/Observers';

        if (!File::exists($observerPath)) {
            return;
        }

        foreach (File::allFiles($observerPath) as $file) {

            $className = $baseNamespace . '\\Observers\\' . $file->getFilenameWithoutExtension();

            if (!class_exists($className)) {
                continue;
            }

            $modelName = str_replace('Observer', '', class_basename($className));
            $modelClass = $baseNamespace . '\\Models\\' . $modelName;

            if (class_exists($modelClass)) {
                $modelClass::observe($className);
            }
        }
    }
}
