<?php

namespace App\Services\Booking;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentInspirationPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

/**
 * The one write path for customer inspiration photos.
 *
 * Processing is synchronous on purpose, unlike the queued service/product
 * photo derivatives: the original upload can carry EXIF GPS (where the
 * customer lives), so it is re-encoded before anything touches disk and no
 * original is ever kept. At most 3 small images per booking keeps the
 * in-request cost bounded, and the booking form already downsizes in the
 * browser before upload.
 */
class InspirationPhotoService
{
    public const MAX_PER_APPOINTMENT = 3;
    public const MAX_UPLOAD_KB       = 10240;
    public const FULL_EDGE           = 1600;
    public const THUMB_EDGE          = 400;

    /** Validation rules for an `inspiration_photos[]` upload with $slots photos still allowed. */
    public static function rules(int $slots = self::MAX_PER_APPOINTMENT, bool $required = false): array
    {
        return [
            'inspiration_photos'   => ($required ? 'required|array|min:1' : 'nullable|array') . '|max:' . max(0, $slots),
            'inspiration_photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:' . self::MAX_UPLOAD_KB,
        ];
    }

    public static function messages(int $slots = self::MAX_PER_APPOINTMENT): array
    {
        return [
            'inspiration_photos.required' => 'Choose at least one photo to add.',
            'inspiration_photos.max'      => $slots < self::MAX_PER_APPOINTMENT
                ? "This booking has room for {$slots} more " . ($slots === 1 ? 'photo' : 'photos') . ' (' . self::MAX_PER_APPOINTMENT . ' in total).'
                : 'You can add up to ' . self::MAX_PER_APPOINTMENT . ' inspiration photos per booking.',
            'inspiration_photos.*.image' => 'Inspiration photos must be images.',
            'inspiration_photos.*.mimes' => 'Inspiration photos must be JPG, PNG or WebP. On iPhone, share the photo first or set Camera to "Most Compatible".',
            'inspiration_photos.*.max'   => 'Each inspiration photo must be smaller than 10 MB.',
        ];
    }

    /**
     * Clean and attach the given uploads. Returns how many were stored; a
     * file that fails to decode is logged and skipped rather than failing the
     * whole request, since by the time this runs the booking already exists.
     *
     * @param  UploadedFile[]  $files
     */
    public function store(Appointment $appointment, array $files): int
    {
        if ($files === []) {
            return 0;
        }

        $manager  = ImageManager::gd();
        $disk     = Storage::disk(AppointmentInspirationPhoto::DISK);
        $base     = "inspiration/{$appointment->tenant_id}/{$appointment->id}";
        $prepared = [];

        foreach ($files as $file) {
            try {
                // read() auto-orients from EXIF before the re-encode drops it.
                $full  = $manager->read($file->getRealPath())->scaleDown(self::FULL_EDGE, self::FULL_EDGE);
                $thumb = $manager->read($file->getRealPath())->cover(self::THUMB_EDGE, self::THUMB_EDGE);

                [$ext, $fullBin]  = $this->encode($full);
                [, $thumbBin]     = $this->encode($thumb);

                $prepared[] = [
                    'stem'   => (string) Str::uuid(),
                    'ext'    => $ext,
                    'full'   => $fullBin,
                    'thumb'  => $thumbBin,
                    'width'  => $full->width(),
                    'height' => $full->height(),
                ];
            } catch (\Throwable $e) {
                Log::warning('Inspiration photo skipped, could not decode upload', [
                    'appointment_id' => $appointment->id,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        if ($prepared === []) {
            return 0;
        }

        // Lock the appointment row so two concurrent uploads can't both see
        // "2 of 3 used" and push the booking past the cap.
        return DB::transaction(function () use ($appointment, $prepared, $disk, $base) {
            Appointment::withoutGlobalScopes()->whereKey($appointment->id)->lockForUpdate()->first();

            $existing = AppointmentInspirationPhoto::withoutGlobalScopes()
                ->where('appointment_id', $appointment->id);
            $count    = (clone $existing)->count();
            $order    = (int) (clone $existing)->max('sort_order');
            $slots    = self::MAX_PER_APPOINTMENT - $count;
            $stored   = 0;

            foreach (array_slice($prepared, 0, max(0, $slots)) as $img) {
                $fullPath  = "{$base}/{$img['stem']}.{$img['ext']}";
                $thumbPath = "{$base}/{$img['stem']}_t.{$img['ext']}";
                $disk->put($fullPath, $img['full']);
                $disk->put($thumbPath, $img['thumb']);

                $photo = new AppointmentInspirationPhoto([
                    'tenant_id'  => $appointment->tenant_id,
                    'path'       => $fullPath,
                    'path_thumb' => $thumbPath,
                    'width'      => $img['width'],
                    'height'     => $img['height'],
                    'sort_order' => $order + (++$stored),
                ]);
                $photo->appointment()->associate($appointment);
                $photo->save();
            }

            return $stored;
        });
    }

    /** WebP where the GD build supports it, JPEG otherwise (same as GeneratePhotoDerivatives). */
    private function encode($image): array
    {
        try {
            return ['webp', (string) $image->toWebp(78)];
        } catch (\Throwable) {
            return ['jpg', (string) $image->toJpeg(82)];
        }
    }
}
