<?php

namespace App\Modules\Booking\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasTenant;
use App\Models\PhotoReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ServicePhoto extends Model
{
    use HasTenant, Auditable;

    /**
     * service_id / hidden_at / hidden_reason stay out of $fillable — they gate
     * ownership and moderation and are only ever set via the relationship foreign
     * key, forceFill(), or the model hooks below. Request input is validated to
     * `photos`/`photos.*` only, so nothing user-supplied reaches create()/update().
     */
    protected $fillable = [
        'tenant_id', 'path', 'disk', 'path_web', 'path_thumb',
        'width', 'height', 'alt_text', 'sort_order', 'is_primary',
    ];

    protected $casts = [
        'service_id' => 'integer',
        'sort_order' => 'integer',
        'width'      => 'integer',
        'height'     => 'integer',
        'is_primary' => 'boolean',
        'hidden_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        // One cover per service. Whenever a row becomes primary, demote its siblings.
        // Sibling maintenance is scoped by service_id (already tenant-specific), so it
        // ignores the tenant global scope — a platform moderator acts across tenants.
        static::saved(function (ServicePhoto $photo) {
            if ($photo->wasChanged('is_primary') && $photo->is_primary) {
                static::withoutGlobalScopes()
                    ->where('service_id', $photo->service_id)
                    ->whereKeyNot($photo->getKey())
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
            static::bustPortalCache($photo);
        });

        // Deleting the cover promotes the next visible photo so the portal is never coverless.
        static::deleted(function (ServicePhoto $photo) {
            if ($photo->is_primary) {
                static::withoutGlobalScopes()
                    ->where('service_id', $photo->service_id)
                    ->whereNull('hidden_at')
                    ->orderBy('sort_order')->orderBy('id')
                    ->first()?->update(['is_primary' => true]);
            }
            static::bustPortalCache($photo);
        });
    }

    protected static function bustPortalCache(ServicePhoto $photo): void
    {
        if ($photo->tenant_id) {
            Cache::forget("book:index:services:{$photo->tenant_id}");
        }
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNull('hidden_at');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function reports()
    {
        return $this->hasMany(PhotoReport::class);
    }

    public function diskName(): string
    {
        return $this->disk ?: 'public';
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }

    /**
     * asset() resolves against the current request host (and honours ASSET_URL for a
     * future CDN), unlike Storage::disk()->url() which is pinned to the configured
     * APP_URL. Matches PropertyImage and works across dev ports / domains.
     */
    public function url(): string
    {
        return asset('storage/' . $this->path);
    }

    /** ~1400px WebP for the gallery stage; falls back to the original until the job runs. */
    public function displayUrl(): string
    {
        return $this->path_web ? asset('storage/' . $this->path_web) : $this->url();
    }

    /** ~600px cropped WebP for cards / thumbnails; falls back to the original. */
    public function thumbUrl(): string
    {
        return $this->path_thumb ? asset('storage/' . $this->path_thumb) : $this->url();
    }

    /** Every stored file for this row (original + derivatives). */
    public function storagePaths(): array
    {
        return array_values(array_filter([$this->path, $this->path_web, $this->path_thumb]));
    }

    public function hide(?string $reason = null): void
    {
        DB::transaction(function () use ($reason) {
            $this->forceFill(['hidden_at' => now(), 'hidden_reason' => $reason])->save();

            // A hidden cover must hand off so the portal still shows something.
            if ($this->is_primary) {
                $this->forceFill(['is_primary' => false])->saveQuietly();
                static::withoutGlobalScopes()
                    ->where('service_id', $this->service_id)
                    ->whereNull('hidden_at')
                    ->orderBy('sort_order')->orderBy('id')
                    ->first()?->update(['is_primary' => true]);
            }
        });

        static::bustPortalCache($this);
    }

    public function unhide(): void
    {
        $this->forceFill(['hidden_at' => null, 'hidden_reason' => null])->save();
    }
}
