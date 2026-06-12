<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCombo extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'valid_from'     => 'datetime',
        'valid_until'    => 'datetime',
        'is_active'      => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(company::class);
    }

    public function services()
    {
        return $this->belongsToMany(product::class, 'service_combo_items');
    }

    public function getTotalServicePriceAttribute(): float
    {
        return $this->services->sum(function ($product) {
            return (float) optional($product->prices->first())->price;
        });
    }

    public function getComboPriceAttribute(): float
    {
        $total = $this->total_service_price;

        if ($this->discount_type === 'percentage') {
            return round($total * (1 - $this->discount_value / 100), 2);
        }

        return max(0, round($total - $this->discount_value, 2));
    }

    public function getSavingsAttribute(): float
    {
        return round($this->total_service_price - $this->combo_price, 2);
    }

    public function isLive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        return true;
    }
}
