<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PlatformService extends Model
{
    protected $fillable = [
        'key', 'name', 'description', 'category', 'billing_type',
        'price', 'price_label', 'icon', 'is_active', 'is_requestable', 'sort_order',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'is_active'      => 'boolean',
        'is_requestable' => 'boolean',
        'sort_order'     => 'integer',
    ];

    public function orders()
    {
        return $this->hasMany(TenantServiceOrder::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function scopeRequestable(Builder $q): Builder
    {
        return $q->where('is_requestable', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    public function displayPrice(): string
    {
        if ($this->price_label) {
            return $this->price_label;
        }

        if ($this->price) {
            $suffix = $this->billing_type === 'recurring' ? '/mo' : '';
            return 'R' . number_format($this->price, 0) . $suffix;
        }

        return 'Custom quote';
    }
}
