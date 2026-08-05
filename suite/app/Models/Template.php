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
        'version',
        'author',
        'changelog',
        'modules_supported',
        'supports_theme_toggle',
        'speed_score',
        'seo_score',
        'accessibility_score',
        'scores_checked_at',
        'rating_override',
        'rating_avg',
        'rating_count',
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
        'price'                 => 'decimal:2',
        'is_active'             => 'boolean',
        'is_visible'            => 'boolean',
        'is_featured'           => 'boolean',
        'sort_order'            => 'integer',
        'changelog'             => 'array',
        'modules_supported'     => 'array',
        'supports_theme_toggle' => 'boolean',
        'speed_score'           => 'integer',
        'seo_score'             => 'integer',
        'accessibility_score'   => 'integer',
        'scores_checked_at'     => 'datetime',
        'rating_override'       => 'decimal:2',
        'rating_avg'            => 'decimal:2',
        'rating_count'          => 'integer',
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

    public function scopeNewest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeRecentlyUpdated(Builder $query): Builder
    {
        return $query->orderByDesc('updated_at');
    }

    public function scopeFeaturedFirst(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')->orderBy('sort_order');
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->withCount(['tenantTemplates as active_customers_count' => function (Builder $q) {
            $q->where('is_active', true);
        }])->orderByDesc('active_customers_count');
    }

    public function scopePremium(Builder $query): Builder
    {
        return $query->where('price_type', '!=', 'free');
    }

    public function scopeTopRated(Builder $query): Builder
    {
        return $query->orderByDesc(\Illuminate\Support\Facades\DB::raw('COALESCE(rating_override, rating_avg, 0)'));
    }

    public function tenantTemplates()
    {
        return $this->hasMany(TenantTemplate::class, 'template_key', 'key');
    }

    public function reviews()
    {
        return $this->hasMany(TemplateReview::class, 'template_key', 'key');
    }

    public function isFree(): bool
    {
        return $this->price_type === 'free';
    }

    public function isPremium(): bool
    {
        return ! $this->isFree();
    }

    public function isComingSoon(): bool
    {
        return ! $this->isFree();
    }

    public function activeCustomersCount(): int
    {
        return $this->attributes['active_customers_count']
            ?? $this->tenantTemplates()->where('is_active', true)->count();
    }

    public function displayRating(): ?float
    {
        $value = $this->rating_override ?? $this->rating_avg;

        return $value !== null ? (float) $value : null;
    }

    public function starsHtml(): string
    {
        $rating = $this->displayRating();

        if ($rating === null) {
            return str_repeat('☆', 5);
        }

        $rounded = (int) round($rating);

        return str_repeat('★', $rounded) . str_repeat('☆', 5 - $rounded);
    }

    /**
     * Recompute rating_avg/rating_count from approved reviews. Called after a
     * review is approved/rejected/deleted so the cached average stays correct
     * without a query-time aggregate on every catalog page load.
     */
    public function recomputeRating(): void
    {
        $approved = $this->reviews()->approved();

        $this->forceFill([
            'rating_avg'   => $approved->avg('rating'),
            'rating_count' => $approved->count(),
        ])->save();
    }
}
