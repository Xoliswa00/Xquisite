<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * Sensitive uploads (payment proofs, rental applicant documents, maintenance
 * and inspection photos from inside people's homes) live on the private
 * `local` disk and are served only through short-lived signed URLs.
 *
 * Why signed URLs rather than a guarded route per viewer: these files are
 * seen by staff, customers, contractors and renters, each on a different
 * auth guard. Authorization already happens when the page that shows the
 * file is rendered; the signature carries that decision to the file request,
 * can't be guessed or enumerated, and expires, so a forwarded or cached link
 * stops working.
 *
 * Files uploaded before this change sat on the `public` disk. The
 * privatize-uploads migration moves them; until it has run, reads and
 * deletes fall back to the public disk so nothing breaks mid-deploy.
 */
class PrivateFile
{
    public const DISK = 'local';

    /** Links stay valid for at least this long... */
    public const LINK_MINUTES = 60;

    /**
     * ...and expiry is rounded up to this bucket, so every render within the
     * same half hour produces the same URL and the browser cache (and 304s)
     * actually work instead of re-downloading every photo on each page view.
     */
    public const LINK_BUCKET_MINUTES = 30;

    /** Top-level folders that hold sensitive uploads (moved by storage:privatize-uploads). */
    public const DIRECTORIES = ['payment_proofs', 'applicant-documents', 'maintenance', 'inspections'];

    /**
     * kind => [model class, path attribute, original-name attribute|null].
     * The kind is part of the signed URL, never trusted on its own.
     */
    public const KINDS = [
        'payment-proof'     => [\App\Modules\Booking\Models\Appointment::class, 'payment_proof_path', 'payment_proof_name'],
        'applicant-doc'     => [\App\Modules\Property\Models\ApplicantDocument::class, 'path', 'original_name'],
        'maintenance-photo' => [\App\Modules\Property\Models\MaintenancePhoto::class, 'path', null],
        'inspection-photo'  => [\App\Modules\Property\Models\InspectionSection::class, 'photo_path', null],
    ];

    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, self::DISK);
    }

    /** Relative signed URL, so it works on custom domains and behind proxies. */
    public static function url(string $kind, int $id): string
    {
        return URL::temporarySignedRoute(
            'private-files.show',
            now()->addMinutes(self::LINK_MINUTES)->ceilMinutes(self::LINK_BUCKET_MINUTES),
            ['kind' => $kind, 'id' => $id],
            absolute: false
        );
    }

    /** The disk currently holding $path: private first, legacy public as a fallback. */
    public static function diskFor(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        foreach ([self::DISK, 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::DISK)->delete($path);
            Storage::disk('public')->delete($path);
        }
    }
}
