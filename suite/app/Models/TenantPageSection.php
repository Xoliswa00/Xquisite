<?php

namespace App\Models;

use App\Services\Website\SectionTypeRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantPageSection extends Model
{
    protected $fillable = [
        'tenant_id',
        'type',
        'variant',
        'content',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'content'    => 'array',
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function registry(): array
    {
        return SectionTypeRegistry::get($this->type);
    }

    public function label(): string
    {
        return $this->registry()['label'] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function resolvedVariant(): string
    {
        return $this->variant ?? SectionTypeRegistry::defaultVariantFor($this->type);
    }
}
