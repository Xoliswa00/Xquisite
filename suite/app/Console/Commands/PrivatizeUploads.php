<?php

namespace App\Console\Commands;

use App\Support\PrivateFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * One-off (idempotent) move of sensitive uploads made before they were
 * private: payment proofs, applicant documents, maintenance and inspection
 * photos, from the public disk (reachable at /storage/...) to the private
 * disk at the same relative path, so stored DB paths keep working.
 *
 * Runs automatically from the 2026_10_03_000002 migration so a deploy can't
 * forget it; safe to re-run by hand.
 */
class PrivatizeUploads extends Command
{
    protected $signature   = 'storage:privatize-uploads';
    protected $description = 'Move sensitive uploads (payment proofs, applicant documents, maintenance/inspection photos) off the public disk';

    public function handle(): int
    {
        $public  = Storage::disk('public');
        $private = Storage::disk(PrivateFile::DISK);
        $moved   = 0;

        foreach (PrivateFile::DIRECTORIES as $dir) {
            foreach ($public->allFiles($dir) as $path) {
                if (! $private->exists($path)) {
                    $private->writeStream($path, $public->readStream($path));
                }
                if ($private->exists($path)) {
                    $public->delete($path);
                    $moved++;
                }
            }
            $public->deleteDirectory($dir);
        }

        $this->info("Moved {$moved} file(s) to private storage.");

        return self::SUCCESS;
    }
}
