<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

/**
 * Generic version of App\Jobs\GenerateServicePhotoDerivatives — same exact
 * algorithm (scale-down web copy, fixed-crop thumb, WebP-with-JPEG-fallback,
 * EXIF stripped as a re-encoding side effect), parameterized by the photo
 * model's class instead of hardcoding ServicePhoto.
 *
 * Deliberately NOT a replacement for GenerateServicePhotoDerivatives —
 * that job has real production traffic behind it and no test coverage;
 * repointing it at this generic version is a separate, low-urgency cleanup
 * for later, not bundled into the first feature (product photos) that
 * needs this logic. Any future second consumer should use this job rather
 * than writing a third copy.
 *
 * The photo model must implement:
 *   - diskName(): string
 *   - storageBasePath(): string   e.g. "products/{$this->product_id}"
 *   - static::find(int $id)
 */
class GeneratePhotoDerivatives implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public int $timeout = 120;

    public function __construct(
        public string $modelClass,
        public int $photoId,
    ) {}

    public function handle(): void
    {
        $photo = $this->modelClass::find($this->photoId);
        if (! $photo) {
            return;
        }

        $disk = Storage::disk($photo->diskName());
        if (! $disk->exists($photo->path)) {
            Log::warning('Photo derivative skipped — source missing', [
                'model'    => $this->modelClass,
                'photo_id' => $photo->id,
                'path'     => $photo->path,
            ]);
            return;
        }

        $manager = ImageManager::gd();
        $base    = $photo->storageBasePath();
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
