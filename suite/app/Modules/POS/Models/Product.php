<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasTenant;
use App\Modules\POS\Services\InventoryService;

class Product extends Model
{
    use HasTenant, SoftDeletes;

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
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'cost_price'          => 'decimal:2',
        'rental_rate'         => 'decimal:2',
        'track_stock'         => 'boolean',
        'is_active'           => 'boolean',
        'is_available_online' => 'boolean',
        'is_rentable'         => 'boolean',
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

    public function getStockStatusAttribute(): string
    {
        if (!$this->track_stock) return 'untracked';
        if ($this->stock_quantity <= 0) return 'out_of_stock';
        if ($this->reorder_level > 0 && $this->stock_quantity <= $this->reorder_level) return 'low';
        return 'ok';
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
