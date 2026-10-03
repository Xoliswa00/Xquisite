<?php

namespace App\Console\Commands;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentInspirationPhoto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Customer inspiration photos are personal data (often a photo of the
 * customer), so they're only kept while they're useful: until --days after
 * the appointment, or until the appointment itself is deleted. Also sweeps
 * directories left behind when an appointment was force-deleted (the DB
 * cascade removes rows without firing the model's file cleanup).
 */
class PruneInspirationPhotos extends Command
{
    protected $signature   = 'booking:prune-inspiration-photos {--days=90 : Days after the appointment to keep its inspiration photos}';
    protected $description = 'Delete customer inspiration photos for appointments that are long past or deleted';

    public function handle(): int
    {
        $cutoff  = now()->subDays((int) $this->option('days'));
        $deleted = 0;

        AppointmentInspirationPhoto::withoutGlobalScopes()
            ->whereIn('appointment_id', Appointment::withoutGlobalScopes()->withTrashed()
                ->where(fn ($q) => $q->where('scheduled_at', '<', $cutoff)->orWhereNotNull('deleted_at'))
                ->select('id'))
            ->chunkById(200, function ($photos) use (&$deleted) {
                foreach ($photos as $photo) {
                    $photo->delete(); // model hook removes the files
                    $deleted++;
                }
            });

        $orphans = $this->sweepOrphanDirectories();

        $this->info("Pruned {$deleted} inspiration photo(s), {$orphans} orphaned folder(s).");

        return self::SUCCESS;
    }

    /** inspiration/{tenant}/{appointment} folders whose appointment row no longer exists. */
    private function sweepOrphanDirectories(): int
    {
        $disk  = Storage::disk(AppointmentInspirationPhoto::DISK);
        $swept = 0;

        foreach ($disk->directories('inspiration') as $tenantDir) {
            $dirs = collect($disk->directories($tenantDir));
            $ids  = $dirs->map(fn ($d) => (int) basename($d))->filter();

            $alive = Appointment::withoutGlobalScopes()->withTrashed()
                ->whereIn('id', $ids)->pluck('id')->all();

            foreach ($dirs as $dir) {
                if (! in_array((int) basename($dir), $alive, true)) {
                    $disk->deleteDirectory($dir);
                    $swept++;
                }
            }
        }

        return $swept;
    }
}
