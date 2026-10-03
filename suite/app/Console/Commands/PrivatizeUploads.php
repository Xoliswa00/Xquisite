<?php

namespace App\Console\Commands;

use App\Support\PrivateFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * One-off (idempotent) move of sensitive uploads made before they were
 * private: payment proofs, applicant documents, maintenance and inspection
 * photos, from the public disk (reachable at /storage/...) to the private
 * disk at the same relative path, so stored DB paths keep working.
 *
 * Safety rules, because these are ID copies and proofs that can't be
 * re-created:
 *  - a file is only removed from the public disk once it verifiably exists
 *    on the private disk;
 *  - a folder is never deleted wholesale, only emptied folders are removed;
 *  - any file that didn't move is listed and the command returns FAILURE.
 * A file left behind is still served (PrivateFile::diskFor falls back to the
 * public disk), so a partial run degrades privacy, never availability.
 */
class PrivatizeUploads extends Command
{
    protected $signature   = 'storage:privatize-uploads {--dirs=* : Folders to move (default: PrivateFile::DIRECTORIES)}';
    protected $description = 'Move sensitive uploads (payment proofs, applicant documents, maintenance/inspection photos) off the public disk';

    public function handle(): int
    {
        $public  = Storage::disk('public');
        $private = Storage::disk(PrivateFile::DISK);
        $dirs    = $this->option('dirs') ?: PrivateFile::DIRECTORIES;
        $moved   = 0;
        $failed  = [];

        foreach ($dirs as $dir) {
            foreach ($public->allFiles($dir) as $path) {
                if ($this->moveOne($public, $private, $path)) {
                    $moved++;
                } else {
                    $failed[] = $path;
                }
            }
            $this->removeEmptyDirectories($public->path($dir));
        }

        $this->info("Moved {$moved} file(s) to private storage.");

        if ($failed !== []) {
            Log::error('storage:privatize-uploads could not move some files; they are still on the public disk', ['files' => $failed]);
            $this->error(count($failed) . ' file(s) could not be moved and are still public:');
            foreach ($failed as $path) {
                $this->line("  {$path}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /** Never throws: any failure (permissions, quota, a bad path) leaves the file public and counts as failed. */
    private function moveOne($public, $private, string $path): bool
    {
        try {
            return $this->attemptMove($public, $private, $path);
        } catch (\Throwable $e) {
            Log::warning('storage:privatize-uploads could not move a file', ['path' => $path, 'error' => $e->getMessage()]);

            return false;
        }
    }

    private function attemptMove($public, $private, string $path): bool
    {
        if (! $private->exists($path)) {
            // Same filesystem (both under storage/app), so a rename is atomic
            // and costs nothing; fall back to a stream copy if it isn't.
            $from = $public->path($path);
            $to   = $private->path($path);
            File::ensureDirectoryExists(dirname($to));

            if (! @rename($from, $to)) {
                $stream = $public->readStream($path);
                $ok     = $stream && $private->writeStream($path, $stream);
                if (is_resource($stream)) {
                    fclose($stream);
                }
                if (! $ok) {
                    return false;
                }
            }
        }

        if (! $private->exists($path)) {
            return false;
        }

        // Already on the private disk (rename, copy or an earlier run): drop the public copy.
        if ($public->exists($path)) {
            $public->delete($path);
        }

        return ! $public->exists($path);
    }

    /** Removes now-empty folders bottom-up; a folder with anything left in it is kept. */
    private function removeEmptyDirectories(string $root): void
    {
        if (! is_dir($root)) {
            return;
        }
        foreach (array_reverse(File::directories($root)) as $dir) {
            $this->removeEmptyDirectories($dir);
        }
        if (count(File::allFiles($root, true)) === 0 && count(File::directories($root)) === 0) {
            @rmdir($root);
        }
    }
}
