<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Modules\Booking\Models\Service;
use Illuminate\Database\Eloquent\Model;

class ServiceCombo extends Model
{
    use HasTenant;
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
        return $this->belongsToMany(Service::class, 'service_combo_items', 'service_combo_id', 'service_id');
    }

    public function getTotalServicePriceAttribute(): float
    {
        return (float) $this->services->sum('price');
    }

    public function getComboPriceAttribute(): float
    {
        $total = $this->total_service_price;
        if ($this->discount_type === 'percentage') {
            return round($total * (1 - $this->discount_value / 100), 2);
        }
        return max(0, $total - $this->discount_value);
    }

    public function getSavingsAttribute(): float
    {
        return round($this->total_service_price - $this->combo_price, 2);
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
