<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

/**
 * No schema change: moves existing payment proofs, applicant documents and
 * maintenance/inspection photos off the public disk as part of the normal
 * `php artisan migrate` deploy step. See App\Console\Commands\PrivatizeUploads.
 */
return new class extends Migration {
    public function up(): void
    {
        Artisan::call('storage:privatize-uploads');
    }

    public function down(): void
    {
        // Deliberately not reversed: these files should never be public again.
    }
};
