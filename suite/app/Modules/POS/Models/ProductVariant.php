<?php

namespace App\Modules\POS\Models;

use App\Models\Traits\Auditable;
use App\Models\Traits\HasTenant;
use App\Modules\POS\Services\InventoryService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One concrete, stocked combination of a product's variant_options — e.g.
 * {"Size":"M","Color":"Red"} for a T-shirt. Stock, price and image can all
 * be overridden per variant; anything left null falls back to the parent
 * Product (see effectivePrice()/effectiveStockQuantity()/effectiveImageUrl()).
 * Stock mutations go through InventoryService's variant methods, mirroring
 * how Product delegates to it — same row-lock-in-a-transaction guarantee.
 */
class ProductVariant extends Model
{
    use HasTenant, Auditable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'sku',
        'attributes',
        'price_override',
        'stock_quantity',
        'reorder_level',
        'reorder_quantity',
        'track_stock',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'attributes'       => 'array',
        'price_override'   => 'decimal:2',
        'stock_quantity'   => 'integer',
        'reorder_level'    => 'integer',
        'reorder_quantity' => 'integer',
        'track_stock'      => 'boolean',
        'is_active'        => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class)->orderByDesc('created_at');
    }

    public function effectivePrice(): float
    {
        return (float) ($this->price_override ?? $this->product->price);
    }

    public function effectiveImageUrl(): ?string
    {
        return $this->image_url ?: $this->product?->image_url;
    }

    /** Demand genuinely differs by size/color — this variant's own reorder_level if set, else the parent product's. */
    public function effectiveReorderLevel(): int
    {
        return (int) ($this->reorder_level ?? $this->product?->reorder_level ?? 0);
    }

    public function effectiveReorderQuantity(): int
    {
        return (int) ($this->reorder_quantity ?? $this->product?->reorder_quantity ?? 0);
    }

    /** "Size: M, Color: Red" — order follows the parent product's declared option order. */
    public function getLabelAttribute(): string
    {
        $order = array_keys($this->product?->variant_options ?? []);
        // getAttribute(), not $this->attributes — the `attributes` DB column
        // collides with Eloquent's own internal $attributes property, so a
        // direct property read here returns the raw internal array (every
        // column, uncast) instead of this column's decoded JSON value.
        $attrs = $this->getAttribute('attributes') ?? [];

        if ($order) {
            $ordered = [];
            foreach ($order as $key) {
                if (array_key_exists($key, $attrs)) {
                    $ordered[$key] = $attrs[$key];
                }
            }
            $attrs = $ordered + $attrs;
        }

        return collect($attrs)->map(fn ($value, $key) => "{$key}: {$value}")->implode(', ');
    }

    public function getNeedsReorderAttribute(): bool
    {
        return $this->track_stock
            && $this->effectiveReorderLevel() > 0
            && $this->stock_quantity <= $this->effectiveReorderLevel();
    }

    public function getStockStatusAttribute(): string
    {
        if (!$this->track_stock) return 'untracked';
        if ($this->stock_quantity <= 0) return 'out_of_stock';
        if ($this->needs_reorder) return 'low';
        return 'ok';
    }

    // ── Stock mutation helpers — mirrors Product's, see InventoryService ──

    public function decrementStock(int $qty, string $type = StockAdjustment::TYPE_SALE, array $extra = []): void
    {
        if (!$this->track_stock) return;

        app(InventoryService::class)->decrementVariant($this, $qty, $type, $extra);
        $this->refresh();
    }

    public function incrementStock(int $qty, string $type = StockAdjustment::TYPE_MANUAL_IN, array $extra = []): void
    {
        app(InventoryService::class)->incrementVariant($this, $qty, $type, $extra);
        $this->refresh();
    }

    public function adjustToCount(int $physicalCount, string $notes = ''): void
    {
        app(InventoryService::class)->setCountVariant($this, $physicalCount, $notes);
        $this->refresh();
    }
}
