<?php

namespace App\Modules\Booking\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A "this is the look I want" photo a customer attaches to their booking.
 *
 * Private by design: stored on the `local` disk (storage/app/private) and only
 * ever served through an authorised controller route, because unlike service
 * or product photos these are personal uploads, often of the customer
 * themselves. Never build a public URL for one (no asset('storage/...')).
 *
 * Written only through App\Services\Booking\InspirationPhotoService, which
 * re-encodes the upload so EXIF/GPS never reaches disk.
 */
class AppointmentInspirationPhoto extends Model
{
    use HasTenant, Auditable;

    public const DISK = 'local';

    /** What the customer asked for (uploaded by them). */
    public const KIND_INSPIRATION = 'inspiration';

    /** How it actually turned out (uploaded by staff, for saved looks). */
    public const KIND_RESULT = 'result';

    /** appointment_id is set via the relationship, never from request input. */
    protected $fillable = ['tenant_id', 'kind', 'path', 'path_thumb', 'width', 'height', 'sort_order'];

    protected $casts = [
        'appointment_id' => 'integer',
        'width'          => 'integer',
        'height'         => 'integer',
        'sort_order'     => 'integer',
    ];

    protected static function booted(): void
    {
        // A DB cascade from a force-deleted appointment skips this hook; the
        // prune command sweeps up those orphaned directories.
        static::deleted(fn (self $photo) => Storage::disk(self::DISK)->delete($photo->storagePaths()));
    }

    public function isResult(): bool
    {
        return $this->kind === self::KIND_RESULT;
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /** Every stored file for this row. */
    public function storagePaths(): array
    {
        return array_values(array_filter([$this->path, $this->path_thumb]));
    }

    /** Stored path for the requested size, falling back to the full copy. */
    public function pathFor(string $size): string
    {
        return $size === 'thumb' && $this->path_thumb ? $this->path_thumb : $this->path;
    }

    /** Staff-side URL (dashboard, behind can:manage-appointments). */
    public function staffUrl(string $size = 'full'): string
    {
        return route('appointments.inspiration.show', [$this->appointment_id, $this->id, $size]);
    }

    /** Customer-side URL (booking portal, behind auth:customer). */
    public function customerUrl(string $slug, string $size = 'full'): string
    {
        return route('book.inspiration.show', [$slug, $this->appointment_id, $this->id, $size]);
    }
}
