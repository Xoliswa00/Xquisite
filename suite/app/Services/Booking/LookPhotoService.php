<?php

namespace App\Services\Booking;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentLookPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

/**
 * The one write path for a booking's look photos: the client's inspiration
 * photos and the staff "after" photos (AppointmentLookPhoto::KIND_*).
 *
 * Processing is synchronous on purpose, unlike the queued service/product
 * photo derivatives: the original upload can carry EXIF GPS (where the
 * customer lives), so it is re-encoded before anything touches disk and no
 * original is ever kept. At most 3 small images per booking keeps the
 * in-request cost bounded, and the booking form already downsizes in the
 * browser before upload.
 */
class LookPhotoService
{
    public const MAX_PER_APPOINTMENT = 3;
    public const MAX_UPLOAD_KB       = 10240;
    public const FULL_EDGE           = 1600;
    public const THUMB_EDGE          = 400;

    /** ~40MP; above this GD decoding risks exhausting memory on shared hosting. */
    public const MAX_PIXELS          = 40_000_000;

    /** Form field for the client's inspiration uploads. */
    public const FIELD_INSPIRATION = 'inspiration_photos';

    /** Form field for the staff "after" uploads. */
    public const FIELD_RESULT = 'result_photos';

    /** Validation rules for a `{$field}[]` upload with $slots photos still allowed. */
    public static function rules(int $slots = self::MAX_PER_APPOINTMENT, bool $required = false, string $field = self::FIELD_INSPIRATION): array
    {
        return [
            $field        => ($required ? 'required|array|min:1' : 'nullable|array') . '|max:' . max(0, $slots),
            "{$field}.*"  => 'image|mimes:jpg,jpeg,png,webp|max:' . self::MAX_UPLOAD_KB,
        ];
    }

    public static function messages(int $slots = self::MAX_PER_APPOINTMENT, string $field = self::FIELD_INSPIRATION): array
    {
        $noun = $field === self::FIELD_RESULT ? 'after' : 'inspiration';

        return [
            "{$field}.required" => 'Choose at least one photo to add.',
            "{$field}.max"      => $slots < self::MAX_PER_APPOINTMENT
                ? "This booking has room for {$slots} more " . ($slots === 1 ? 'photo' : 'photos') . ' (' . self::MAX_PER_APPOINTMENT . ' in total).'
                : 'You can add up to ' . self::MAX_PER_APPOINTMENT . " {$noun} photos per booking.",
            "{$field}.*.image" => ucfirst($noun) . ' photos must be images.',
            "{$field}.*.mimes" => ucfirst($noun) . ' photos must be JPG, PNG or WebP. On iPhone, share the photo first or set Camera to "Most Compatible".',
            "{$field}.*.max"   => 'Each ' . $noun . ' photo must be smaller than 10 MB.',
        ];
    }

    /**
     * Clean and attach the given uploads. Returns how many were stored; a
     * file that fails to decode is logged and skipped rather than failing the
     * whole request, since by the time this runs the booking already exists.
     *
     * $kind is inspiration (customer uploads) or result (staff "after" photos
     * for a saved look); the 3-photo cap applies to each kind separately.
     *
     * @param  UploadedFile[]  $files
     */
    public function store(Appointment $appointment, array $files, string $kind = AppointmentLookPhoto::KIND_INSPIRATION): int
    {
        if ($files === []) {
            return 0;
        }

        $manager  = ImageManager::gd();
        $disk     = Storage::disk(AppointmentLookPhoto::DISK);
        $base     = "inspiration/{$appointment->tenant_id}/{$appointment->id}";
        $prepared = [];

        foreach ($files as $file) {
            try {
                // A raw 48MP phone photo needs ~200MB of GD memory to decode;
                // refuse anything that big before decoding rather than OOM the worker.
                $size = @getimagesize($file->getRealPath());
                if (! $size || $size[0] * $size[1] > self::MAX_PIXELS) {
                    throw new \RuntimeException('Image missing dimensions or larger than ' . self::MAX_PIXELS . ' pixels');
                }

                // Decode once: read() auto-orients from EXIF before the re-encode
                // drops it, and the thumb is cut from the already-scaled copy.
                $full  = $manager->read($file->getRealPath())->scaleDown(self::FULL_EDGE, self::FULL_EDGE);
                $thumb = (clone $full)->cover(self::THUMB_EDGE, self::THUMB_EDGE);

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
        return DB::transaction(function () use ($appointment, $prepared, $disk, $base, $kind) {
            Appointment::withoutGlobalScopes()->whereKey($appointment->id)->lockForUpdate()->first();

            $existing = AppointmentLookPhoto::withoutGlobalScopes()
                ->where('appointment_id', $appointment->id);
            $count    = (clone $existing)->where('kind', $kind)->count();
            $order    = (int) (clone $existing)->max('sort_order');
            $slots    = self::MAX_PER_APPOINTMENT - $count;
            $stored   = 0;

            foreach (array_slice($prepared, 0, max(0, $slots)) as $img) {
                $fullPath  = "{$base}/{$img['stem']}.{$img['ext']}";
                $thumbPath = "{$base}/{$img['stem']}_t.{$img['ext']}";
                $disk->put($fullPath, $img['full']);
                $disk->put($thumbPath, $img['thumb']);

                $photo = new AppointmentLookPhoto([
                    'tenant_id'  => $appointment->tenant_id,
                    'kind'       => $kind,
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

    /**
     * "Book this look again": copy a saved look's photos onto a new booking
     * as its inspiration, after-photos first (that's the look they liked),
     * up to the free inspiration slots. Files are copied, not shared, so
     * pruning or removing one booking never breaks the other.
     */
    public function copyLook(Appointment $from, Appointment $to): int
    {
        $disk  = Storage::disk(AppointmentLookPhoto::DISK);
        $base  = "inspiration/{$to->tenant_id}/{$to->id}";
        $slots = self::MAX_PER_APPOINTMENT - $to->inspirationPhotos()->count();
        if ($slots <= 0) {
            return 0;
        }

        $source = $from->orderedLookPhotos()->take($slots);

        $order  = (int) $to->lookPhotos()->max('sort_order');
        $copied = 0;

        foreach ($source as $photo) {
            if (! $disk->exists($photo->path)) {
                continue;
            }
            $stem      = (string) Str::uuid();
            $ext       = pathinfo($photo->path, PATHINFO_EXTENSION);
            $fullPath  = "{$base}/{$stem}.{$ext}";
            $thumbPath = $photo->path_thumb ? "{$base}/{$stem}_t." . pathinfo($photo->path_thumb, PATHINFO_EXTENSION) : null;

            // The disk doesn't throw on failure, so check: a row pointing at a
            // missing file would show the stylist a broken image.
            if (! $disk->copy($photo->path, $fullPath)) {
                continue;
            }
            if ($thumbPath && (! $disk->exists($photo->path_thumb) || ! $disk->copy($photo->path_thumb, $thumbPath))) {
                $thumbPath = null; // pathFor('thumb') falls back to the full copy
            }

            $copy = new AppointmentLookPhoto([
                'tenant_id'  => $to->tenant_id,
                'kind'       => AppointmentLookPhoto::KIND_INSPIRATION,
                'path'       => $fullPath,
                'path_thumb' => $thumbPath,
                'width'      => $photo->width,
                'height'     => $photo->height,
                'sort_order' => $order + (++$copied),
            ]);
            $copy->appointment()->associate($to);
            $copy->save();
        }

        return $copied;
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
