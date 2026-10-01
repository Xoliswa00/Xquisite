<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use App\Modules\Booking\Models\Service;
use App\Modules\POS\Models\Product;
use Illuminate\Database\Eloquent\Model;

class ServiceCombo extends Model
{
    use HasTenant, Auditable;
    protected $fillable = [
        'tenant_id', 'name', 'description', 'discount_type',
        'discount_value', 'valid_from', 'valid_until', 'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'valid_from'     => 'datetime',
        'valid_until'    => 'datetime',
        'is_active'      => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'combo_items', 'service_combo_id', 'service_id')
            ->whereNotNull('combo_items.service_id');
    }

    /**
     * Restored — combo_items originally had a product_id column
     * (2026_06_12_000001), which a later migration dropped in favour of
     * services-only. Both relations share one pivot table, each scoped to
     * its own non-null FK column, so sync() on one never disturbs rows
     * belonging to the other (its detach step only matches rows already
     * satisfying this relation's own whereNotNull).
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'combo_items', 'service_combo_id', 'product_id')
            ->whereNotNull('combo_items.product_id');
    }

    /**
     * Kept meaning "services only" — the booking funnel's own combo
     * auto-detection (PublicBookingController::detectCombo()) and pricing
     * were built against this exact semantic before products existed
     * here, and a pure-service combo (still the common case) must keep
     * computing identically. Use total_price for anything that needs to
     * include products too.
     */
    public function getTotalServicePriceAttribute(): float
    {
        return (float) $this->services->sum('price');
    }

    public function getTotalProductPriceAttribute(): float
    {
        return (float) $this->products->sum('price');
    }

    /**
     * total_service_price + total_product_price — for a services-only
     * combo this is identical to total_service_price (total_product_price
     * is 0), so combo_price/savings below produce the exact same numbers
     * as before this class gained product support.
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->total_service_price + $this->total_product_price;
    }

    public function getComboPriceAttribute(): float
    {
        $total = $this->total_price;
        if ($this->discount_type === 'percentage') {
            return round($total * (1 - $this->discount_value / 100), 2);
        }
        return max(0, $total - $this->discount_value);
    }

    public function getSavingsAttribute(): float
    {
        return round($this->total_price - $this->combo_price, 2);
    }

    /** A combo only belongs on the storefront once it includes at least one sellable product. */
    public function scopeHasProducts($query)
    {
        return $query->whereHas('products');
    }

    public function isLive(): bool
    {
        if (!$this->is_active) return false;
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) return false;
        if ($this->valid_until && $now->gt($this->valid_until)) return false;
        return true;
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) return 'Inactive';
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) return 'Scheduled';
        if ($this->valid_until && $now->gt($this->valid_until)) return 'Expired';
        return 'Live';
    }
}
