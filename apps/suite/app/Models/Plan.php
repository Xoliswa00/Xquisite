<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'key',
        'name',
        'tagline',
        'description',
        'price_monthly',
        'price_annual',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_annual'  => 'decimal:2',
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function moduleKeys(): array
    {
        return $this->planModules()->pluck('module_key')->all();
    }

    public function planModules()
    {
        return $this->hasMany(PlanModule::class);
    }

    public function platformModules()
    {
        return $this->hasManyThrough(
            PlatformModule::class,
            PlanModule::class,
            'plan_id',
            'key',
            'id',
            'module_key'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('price_monthly');
    }

    public function annualSaving(): ?float
    {
        if (! $this->price_annual) {
            return null;
        }

        return round(($this->price_monthly * 12) - ($this->price_annual * 12), 2);
    }

    public function annualDiscountPercent(): ?int
    {
        if (! $this->price_annual || $this->price_monthly == 0) {
            return null;
        }

        return (int) round((1 - ($this->price_annual / $this->price_monthly)) * 100);
    }
}
