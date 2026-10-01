<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use App\Modules\POS\Services\InventoryService;

class Product extends Model
{
    use HasTenant, Auditable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'category',
        'service_category_id',
        'duration_minutes',
        'description',
        'price',
        'cost_price',
        'stock_quantity',
        'track_stock',
        'reorder_level',
        'reorder_quantity',
        'supplier',
        'supplier_sku',
        'supplier_id',
        'is_active',
        'image_url',
        'is_available_online',
        'is_rentable',
        'rental_rate',
        'total_units',
        'condition',
        'variant_options',
        'has_variants',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'cost_price'          => 'decimal:2',
        'rental_rate'         => 'decimal:2',
        'track_stock'         => 'boolean',
        'is_active'           => 'boolean',
        'is_available_online' => 'boolean',
        'is_rentable'         => 'boolean',
        'variant_options'     => 'array',
        'has_variants'        => 'boolean',
    ];

    public function rentalOrders()
    {
        return $this->hasMany(\App\Models\RentalOrder::class);
    }

    public function unitsAvailable(?string $onDate = null): int
    {
        if (! $this->is_rentable || ! $this->total_units) return 0;
        $date = $onDate ?? now()->toDateString();
        $out = $this->rentalOrders()
            ->whereIn('status', ['reserved', 'out'])
            ->where('event_date', '<=', $date)
            ->where('return_due_at', '>=', $date)
            ->sum('quantity');
        return max(0, $this->total_units - $out);
    }

    // ── Relationships ──────────────────────────────────────────

    public function serviceCategory()
    {
        return $this->belongsTo(\App\Models\ServiceCategory::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class)->orderByDesc('created_at');
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function activeVariants()
    {
        return $this->variants()->where('is_active', true);
    }

    /** Max uploaded photos per product — same cap as Service::MAX_PHOTOS, same reasoning (keeps upload/derivative cost bounded). */
    public const MAX_PHOTOS = 5;

    public function photos()
    {
        return $this->hasMany(ProductPhoto::class);
    }

    /** Admin view: every photo (including moderator-hidden), cover first. */
    public function photosOrdered()
    {
        return $this->hasMany(ProductPhoto::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Storefront/POS: only photos not hidden by moderation, cover first. */
    public function visiblePhotos()
    {
        return $this->hasMany(ProductPhoto::class)
            ->whereNull('hidden_at')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Just the cover — cheap single-row eager load for listing pages (avoids an N+1 vs. loading all visiblePhotos). */
    public function coverPhoto()
    {
        return $this->hasOne(ProductPhoto::class)
            ->whereNull('hidden_at')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** The single photo shown anywhere only one image fits (listing card, POS tile, related-product card). */
    public function getDisplayPhotoAttribute(): ?ProductPhoto
    {
        $photos = $this->relationLoaded('visiblePhotos') ? $this->visiblePhotos : $this->visiblePhotos()->get();

        return $photos->firstWhere('is_primary', true) ?? $photos->first();
    }

    // ── Computed ───────────────────────────────────────────────

    public function getDurationLabelAttribute(): ?string
    {
        if (!$this->duration_minutes) return null;
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;
        if ($h > 0 && $m > 0) return "{$h}h {$m}min";
        if ($h > 0) return "{$h}h";
        return "{$m}min";
    }

    public function getNeedsReorderAttribute(): bool
    {
        return $this->track_stock
            && $this->reorder_level > 0
            && $this->stock_quantity <= $this->reorder_level;
    }

    /**
     * Once a product has variants, its own stock_quantity is not
     * authoritative — each variant tracks its own. totalVariantStock()
     * is the sum, used for listings/badges that just need "is this in
     * stock at all" without caring which variant.
     */
    public function totalVariantStock(): int
    {
        return $this->variants()->where('track_stock', true)->sum('stock_quantity');
    }

    /**
     * Single source of truth for "how many things need reordering right
     * now" — plain products below their own reorder_level, plus variants
     * below theirs (has_variants products are excluded from the plain
     * half; their own reorder_level/stock_quantity aren't authoritative).
     * Used by the sidebar badge, the dashboard low-stock card, and
     * StockController::reorderAlerts() — previously each computed this
     * independently and only the plain-product half, so the sidebar badge
     * silently undercounted the moment any variant went low (found while
     * verifying the variant reorder-alerts page: badge said 1, page said 2).
     */
    public static function reorderAlertCount(): int
    {
        $plain = static::where('track_stock', true)
            ->where('has_variants', false)
            ->where('reorder_level', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->count();

        // Can't express in a single whereColumn — a variant's effective
        // reorder_level falls back to its parent product's when null, same
        // reasoning as StockController::trackedStockRows().
        $variants = ProductVariant::where('track_stock', true)
            ->where('is_active', true)
            ->with('product')
            ->get()
            ->filter(fn (ProductVariant $v) => $v->needs_reorder)
            ->count();

        return $plain + $variants;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->has_variants) {
            $tracked = $this->variants()->where('track_stock', true);
            if (! $tracked->exists()) return 'untracked';
            if ($tracked->sum('stock_quantity') <= 0) return 'out_of_stock';
            if ($this->variants()->where('track_stock', true)->get()->contains(fn ($v) => $v->needs_reorder)) return 'low';
            return 'ok';
        }

        if (!$this->track_stock) return 'untracked';
        if ($this->stock_quantity <= 0) return 'out_of_stock';
        if ($this->reorder_level > 0 && $this->stock_quantity <= $this->reorder_level) return 'low';
        return 'ok';
    }

    /**
     * Distinct color values across this product's active variants, for a
     * listing-card swatch preview — "comes in 3 colors" is a decision a
     * shopper can make before ever opening the product page. Reads
     * whichever variant_options axis is named "Color" case-insensitively
     * (tenants type their own axis names when capturing a product; see
     * ProductVariant's class doc), so a tenant who typed "colour" or
     * "COLOR" still gets swatches. Returns [] for a plain product or one
     * whose variants don't have a color-like axis at all (e.g. Size only).
     */
    public function swatchColors(): array
    {
        if (!$this->has_variants) {
            return [];
        }

        $colorAxis = collect($this->variant_options ?? [])
            ->keys()
            ->first(fn ($name) => strtolower($name) === 'color' || strtolower($name) === 'colour');

        if (!$colorAxis) {
            return [];
        }

        return $this->activeVariants
            ->pluck("attributes.{$colorAxis}")
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Storefront gallery images. Previously a 1-item-array wrapper around
     * the single legacy image_url column (dormant scaffolding, documented
     * as such) — now returns the real uploaded set via ProductPhoto, cover
     * first, falling back to the legacy pasted URL for a product that
     * never got real photos uploaded. Every Blade view that already loops
     * over gallery_images (built for this exact day) picks up real
     * multi-image galleries with no view changes.
     */
    public function getGalleryImagesAttribute(): array
    {
        $photos = $this->relationLoaded('visiblePhotos') ? $this->visiblePhotos : $this->visiblePhotos()->get();

        if ($photos->isNotEmpty()) {
            return $photos->map(fn (ProductPhoto $p) => $p->displayUrl())->all();
        }

        $legacy = $this->getRawOriginal('image_url');

        return $legacy ? [$legacy] : [];
    }

    /**
     * Overrides the raw image_url column read: once a product has an
     * uploaded cover photo, every existing call site that reads
     * $product->image_url directly (shop index cards, Quick View,
     * related-product cards, the has_variants branch's static fallback
     * image) picks it up automatically — no call site needed to change.
     * displayUrl() (the ~1400px derivative) rather than thumbUrl(), since
     * call sites vary from small cards to a larger PDP-equivalent preview
     * and all of them already apply object-cover in CSS; a single
     * reasonably-sized source scales down cleanly everywhere, where a
     * fixed 3:2 thumb crop forced into a square container would distort.
     * Falls back to $value (the actual raw column) for a product that
     * never got real photos uploaded — the legacy pasted-URL path keeps
     * working exactly as before, untouched.
     */
    public function getImageUrlAttribute(?string $value): ?string
    {
        $cover = $this->relationLoaded('coverPhoto') ? $this->coverPhoto : $this->coverPhoto()->first();

        return $cover?->displayUrl() ?? $value;
    }

    // ── Stock mutation helpers ─────────────────────────────────

    // Stock mutations are delegated to InventoryService, which row-locks the
    // product inside a transaction so stock can never be corrupted or go
    // negative. These thin wrappers keep existing callers working; refresh()
    // syncs this in-memory instance with the locked write.

    public function decrementStock(int $qty, string $type = StockAdjustment::TYPE_SALE, array $extra = []): void
    {
        if (!$this->track_stock) return;

        app(InventoryService::class)->decrement($this, $qty, $type, $extra);
        $this->refresh();
    }

    public function incrementStock(int $qty, string $type = StockAdjustment::TYPE_MANUAL_IN, array $extra = []): void
    {
        app(InventoryService::class)->increment($this, $qty, $type, $extra);
        $this->refresh();
    }

    public function adjustToCount(int $physicalCount, string $notes = ''): void
    {
        app(InventoryService::class)->setCount($this, $physicalCount, $notes);
        $this->refresh();
    }
}
