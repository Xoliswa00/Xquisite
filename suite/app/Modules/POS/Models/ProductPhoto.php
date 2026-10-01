<?php

namespace App\Modules\POS\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Mirrors App\Modules\Booking\Models\ServicePhoto's pattern exactly (cover
 * invariant via model hooks, hide/unhide under a transaction, derivative-
 * URL accessors with graceful fallback) — deliberately a sibling class,
 * not a shared trait/base with ServicePhoto. That class has real
 * production data and an active moderation workflow with zero existing
 * test coverage; refactoring it to extract shared behavior at the same
 * time a brand-new feature is being built is a separate, higher-risk
 * change with no upside here. If a third photo-owning entity shows up
 * later, that's the right time to extract a trait covering both of these
 * already-proven-stable classes.
 *
 * No bustPortalCache() — unlike the booking funnel's
 * "book:index:services:{tenant}" cache, the shop's product listing
 * (StorefrontController::index()) isn't cached today, so there is nothing
 * to invalidate. Add one here (and a corresponding Cache::remember() on
 * the storefront) only if that changes.
 */
class ProductPhoto extends Model
{
    use HasTenant, Auditable;

    /**
     * product_id / hidden_at / hidden_reason stay out of $fillable — same
     * reasoning as ServicePhoto: they gate ownership and moderation and
     * are only ever set via the relationship foreign key, forceFill(), or
     * the model hooks below. Request input is validated to
     * `photos`/`photos.*` only, so nothing user-supplied reaches
     * create()/update().
     */
    protected $fillable = [
        'tenant_id', 'path', 'disk', 'path_web', 'path_thumb',
        'width', 'height', 'alt_text', 'sort_order', 'is_primary',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'sort_order' => 'integer',
        'width'      => 'integer',
        'height'     => 'integer',
        'is_primary' => 'boolean',
        'hidden_at'  => 'datetime',
    ];

    protected static function booted(): void
    {
        // One cover per product. Whenever a row becomes primary, demote its siblings.
        static::saved(function (ProductPhoto $photo) {
            if ($photo->wasChanged('is_primary') && $photo->is_primary) {
                static::withoutGlobalScopes()
                    ->where('product_id', $photo->product_id)
                    ->whereKeyNot($photo->getKey())
                    ->where('is_primary', true)
                    ->update(['is_primary' => false]);
            }
        });

        // Deleting the cover promotes the next visible photo so a listing/PDP is never coverless.
        static::deleted(function (ProductPhoto $photo) {
            if ($photo->is_primary) {
                static::withoutGlobalScopes()
                    ->where('product_id', $photo->product_id)
                    ->whereNull('hidden_at')
                    ->orderBy('sort_order')->orderBy('id')
                    ->first()?->update(['is_primary' => true]);
            }
        });
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->whereNull('hidden_at');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function diskName(): string
    {
        return $this->disk ?: 'public';
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }

    /** services/{id} → products/{id}, consumed by GeneratePhotoDerivatives. */
    public function storageBasePath(): string
    {
        return 'products/' . $this->product_id;
    }

    /**
     * asset() resolves against the current request host (and honours
     * ASSET_URL for a future CDN), unlike Storage::disk()->url() which is
     * pinned to the configured APP_URL. Matches ServicePhoto/PropertyImage.
     */
    public function url(): string
    {
        return asset('storage/' . $this->path);
    }

    /** ~1400px WebP for the PDP gallery; falls back to the original until the job runs. */
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

            // A hidden cover must hand off so something is always shown.
            if ($this->is_primary) {
                $this->forceFill(['is_primary' => false])->saveQuietly();
                static::withoutGlobalScopes()
                    ->where('product_id', $this->product_id)
                    ->whereNull('hidden_at')
                    ->orderBy('sort_order')->orderBy('id')
                    ->first()?->update(['is_primary' => true]);
            }
        });
    }

    public function unhide(): void
    {
        $this->forceFill(['hidden_at' => null, 'hidden_reason' => null])->save();
    }
}
