<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;


class Service extends Model
{
    use HasTenant, Auditable, SoftDeletes;

    /** Maximum photos a single service may hold. */
    public const MAX_PHOTOS = 5;

    protected $fillable = [
        'tenant_id',
        'name',
        'service_category_id',
        'description',
        'duration_minutes',
        'price',
        'cost_price',
        'pricing_type',
        'price_per_unit',
        'unit_label',
        'is_active',
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'cost_price'       => 'decimal:2',
        'price_per_unit'   => 'decimal:2',
        'duration_minutes' => 'integer',
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

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function category()
    {
        return $this->belongsTo(\App\Models\ServiceCategory::class, 'service_category_id');
    }

    public function serviceProducts()
    {
        return $this->hasMany(ServiceProduct::class);
    }

    /** All photos, no ordering — safe for writes/aggregates. */
    public function photos()
    {
        return $this->hasMany(ServicePhoto::class);
    }

    /** Admin view: every photo (including moderator-hidden), cover first. */
    public function photosOrdered()
    {
        return $this->hasMany(ServicePhoto::class)
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Public portal: only photos not hidden by moderation, cover first. */
    public function visiblePhotos()
    {
        return $this->hasMany(ServicePhoto::class)
            ->whereNull('hidden_at')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** Just the cover — cheap single-row eager load for list views. */
    public function coverPhoto()
    {
        return $this->hasOne(ServicePhoto::class)
            ->whereNull('hidden_at')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /** The single photo shown on the public booking portal (the "cover"). */
    public function getDisplayPhotoAttribute(): ?ServicePhoto
    {
        $photos = $this->relationLoaded('visiblePhotos') ? $this->visiblePhotos : $this->visiblePhotos()->get();

        return $photos->firstWhere('is_primary', true) ?? $photos->first();
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
