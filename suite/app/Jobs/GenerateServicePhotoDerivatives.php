<?php

namespace App\Jobs;

use App\Modules\Booking\Models\ServicePhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

/**
 * Builds the two derivatives every ServicePhoto surface actually needs:
 *   - path_web   ~1400px WebP for the full-screen gallery stage
 *   - path_thumb  600x400 cropped WebP for cards, the gallery strip, admin grid, step-2 avatar
 * Re-encoding also strips EXIF (GPS/camera metadata) from the public copies.
 * Templates fall back to the original until this runs, so it is safe to queue.
 */
class GenerateServicePhotoDerivatives implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 120;

    public function __construct(public int $photoId) {}

    public function handle(): void
    {
        $photo = ServicePhoto::find($this->photoId);
        if (! $photo) {
            return;
        }

        $disk = Storage::disk($photo->diskName());
        if (! $disk->exists($photo->path)) {
            Log::warning('ServicePhoto derivative skipped — source missing', ['photo_id' => $photo->id, 'path' => $photo->path]);
            return;
        }

        $manager = ImageManager::gd();
        $base    = 'services/' . $photo->service_id;
        $stem    = pathinfo($photo->path, PATHINFO_FILENAME);

        // Gallery stage — scale down only, keep aspect.
        $web = $manager->read($disk->get($photo->path))->scaleDown(width: 1400);
        [$webExt, $webBin] = $this->encode($web);
        $webPath = "{$base}/{$stem}_w.{$webExt}";
        $disk->put($webPath, $webBin);

        // Cards / thumbnails — fixed 3:2 crop.
        $thumb = $manager->read($disk->get($photo->path))->cover(600, 400);
        [$thumbExt, $thumbBin] = $this->encode($thumb);
        $thumbPath = "{$base}/{$stem}_t.{$thumbExt}";
        $disk->put($thumbPath, $thumbBin);

        $photo->forceFill([
            'path_web'   => $webPath,
            'path_thumb' => $thumbPath,
            'width'      => $web->width(),
            'height'     => $web->height(),
        ])->saveQuietly();
    }

    /** WebP where the GD build supports it, JPEG otherwise. */
    private function encode($image): array
    {
        try {
            return ['webp', (string) $image->toWebp(72)];
        } catch (\Throwable) {
            return ['jpg', (string) $image->toJpeg(78)];
        }
    }
}
