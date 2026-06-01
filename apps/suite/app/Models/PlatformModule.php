<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PlatformModule extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'icon',
        'price',
        'status',
        'launch_date',
        'sort_order',
        'is_visible',
        'auto_activate',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'launch_date'   => 'date',
        'is_visible'    => 'boolean',
        'auto_activate' => 'boolean',
        'sort_order'    => 'integer',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeBeta(Builder $query): Builder
    {
        return $query->where('status', 'beta');
    }

    public function scopeComingSoon(Builder $query): Builder
    {
        return $query->where('status', 'coming_soon');
    }

    public function isLive(): bool
    {
        return $this->status === 'active';
    }

    public function isBeta(): bool
    {
        return $this->status === 'beta';
    }

    public function isComingSoon(): bool
    {
        return $this->status === 'coming_soon';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'       => 'Live',
            'beta'         => 'In Testing',
            'coming_soon'  => 'Coming Soon',
            default        => ucfirst($this->status),
        };
    }

    public function getStatusColourAttribute(): string
    {
        return match ($this->status) {
            'active'       => 'emerald',
            'beta'         => 'amber',
            'coming_soon'  => 'indigo',
            default        => 'gray',
        };
    }
}
