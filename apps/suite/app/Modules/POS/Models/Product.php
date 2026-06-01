<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Product extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'category',
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

    public function decrementStock(int $qty, string $type = StockAdjustment::TYPE_SALE, array $extra = []): void
    {
        if (!$this->track_stock) return;

        $before = $this->stock_quantity;
        $after  = max(0, $before - $qty);

        $this->update(['stock_quantity' => $after]);

        StockAdjustment::create(array_merge([
            'product_id'      => $this->id,
            'type'            => $type,
            'quantity_before' => $before,
            'quantity_change' => -$qty,
            'quantity_after'  => $after,
            'created_by'      => auth()->id(),
        ], $extra));
    }

    public function incrementStock(int $qty, string $type = StockAdjustment::TYPE_MANUAL_IN, array $extra = []): void
    {
        $before = $this->stock_quantity;
        $after  = $before + $qty;

        $this->update(['stock_quantity' => $after]);

        StockAdjustment::create(array_merge([
            'product_id'      => $this->id,
            'type'            => $type,
            'quantity_before' => $before,
            'quantity_change' => +$qty,
            'quantity_after'  => $after,
            'created_by'      => auth()->id(),
        ], $extra));
    }

    public function adjustToCount(int $physicalCount, string $notes = ''): void
    {
        $before = $this->stock_quantity;
        $change = $physicalCount - $before;

        $this->update(['stock_quantity' => $physicalCount]);

        StockAdjustment::create([
            'product_id'      => $this->id,
            'type'            => StockAdjustment::TYPE_STOCKTAKE,
            'quantity_before' => $before,
            'quantity_change' => $change,
            'quantity_after'  => $physicalCount,
            'notes'           => $notes,
            'created_by'      => auth()->id(),
        ]);
    }
}
