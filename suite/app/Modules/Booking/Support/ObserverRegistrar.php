<?php

namespace App\Modules\Booking\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ObserverRegistrar
{
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
