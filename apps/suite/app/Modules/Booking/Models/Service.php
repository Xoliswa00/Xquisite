<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;


class Service extends Model
{
    use HasTenant, Auditable, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'duration_minutes',
        'price',
        'pricing_type',
        'price_per_unit',
        'unit_label',
        'is_active',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'price_per_unit' => 'decimal:2',
    ];

    public function isPerHead(): bool   { return $this->pricing_type === 'per_head'; }
    public function isPerUnit(): bool   { return $this->pricing_type === 'per_unit'; }

    public function calculatePrice(int $quantity = 1): float
    {
        return match ($this->pricing_type) {
            'per_head', 'per_unit' => round($this->price_per_unit * $quantity, 2),
            default                => (float) $this->price,
        };
    }

    public function priceLabel(): string
    {
        return match ($this->pricing_type) {
            'per_head' => 'R' . number_format($this->price_per_unit, 2) . ' ' . ($this->unit_label ?? 'per person'),
            'per_unit' => 'R' . number_format($this->price_per_unit, 2) . ' ' . ($this->unit_label ?? 'per unit'),
            default    => 'R' . number_format($this->price, 2),
        };
    }

    public function serviceProducts()
    {
        return $this->hasMany(ServiceProduct::class);
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class, 'staff_services');
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
