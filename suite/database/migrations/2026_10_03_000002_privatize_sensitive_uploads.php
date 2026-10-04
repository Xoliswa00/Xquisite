<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * No schema change: moves existing payment proofs, applicant documents and
 * maintenance/inspection photos off the public disk as part of the normal
 * `php artisan migrate` deploy step. See App\Console\Commands\PrivatizeUploads.
 *
 * - The folder list is frozen here so this migration keeps doing the same
 *   thing even if PrivateFile::DIRECTORIES changes later.
 * - Skipped under tests: RefreshDatabase migrates before a test can fake
 *   storage, so it would otherwise move a developer's real files.
 * - Never fails the deploy: anything that couldn't move stays on the public
 *   disk (still served via the fallback) and is logged as an error, so the
 *   remaining migrations still run. Re-run the command by hand to finish.
 */
return new class extends Migration {
    private const DIRECTORIES = ['payment_proofs', 'applicant-documents', 'maintenance', 'inspections'];

    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $status = Artisan::call('storage:privatize-uploads', ['--dirs' => self::DIRECTORIES]);

        if ($status !== 0) {
            Log::error('privatize_sensitive_uploads migration: some files were not moved. Run `php artisan storage:privatize-uploads` again.', [
                'output' => Artisan::output(),
            ]);
        }
    }

    public function down(): void
    {
        // Deliberately not reversed: these files should never be public again.
    }
};
