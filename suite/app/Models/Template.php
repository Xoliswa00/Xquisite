<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'category',
        'preview_image_url',
        'blade_view',
        'price_type',
        'price',
        'default_primary_color',
        'default_secondary_color',
        'default_accent_color',
        'is_active',
        'is_visible',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price'       => 'decimal:2',
        'is_active'   => 'boolean',
        'is_visible'  => 'boolean',
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeFree(Builder $query): Builder
    {
        return $query->where('price_type', 'free');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function tenantTemplates()
    {
        return $this->hasMany(TenantTemplate::class, 'template_key', 'key');
    }

    public function isFree(): bool
    {
        return $this->price_type === 'free';
    }

    public function isComingSoon(): bool
    {
        return ! $this->isFree();
    }
}
