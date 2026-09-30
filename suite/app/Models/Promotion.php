<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasTenant, Auditable;
    protected $fillable = [
        'tenant_id', 'name', 'description', 'code', 'discount_type',
        'discount_value', 'applies_to', 'valid_from', 'valid_until',
        'max_uses', 'used_count', 'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'valid_from'     => 'datetime',
        'valid_until'    => 'datetime',
        'max_uses'       => 'integer',
        'used_count'     => 'integer',
        'is_active'      => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Single lookup path for both the cart's live preview (CartController)
     * and checkout's final, authoritative revalidation (OrderService) — so
     * the two can never drift on what counts as a valid, usable code.
     * Returns null for a code that's unknown, inactive, expired, exhausted,
     * or scoped to 'services' only (the booking funnel's own promo/check
     * route is the only redemption path for those).
     */
    public static function findUsable(int $tenantId, string $code): ?self
    {
        $promotion = static::where('tenant_id', $tenantId)
            ->whereRaw('UPPER(code) = ?', [strtoupper(trim($code))])
            ->first();

        if (!$promotion || !$promotion->isLive() || !$promotion->appliesToProducts()) {
            return null;
        }

        return $promotion;
    }

    public function isLive(): bool
    {
        if (!$this->is_active) return false;
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) return false;
        if ($this->valid_until && $now->gt($this->valid_until)) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        return true;
    }

    /**
     * Whether this promotion is usable against a shop (Product-selling)
     * checkout — 'all' or 'products' only. 'services' is the booking
     * funnel's own scope (see the promo/check route under book/{slug});
     * the two funnels deliberately never share a redemption path, so a
     * services-only code is invisible to the shop's applyPromo() flow.
     */
    public function appliesToProducts(): bool
    {
        return in_array($this->applies_to, ['all', 'products'], true);
    }

    /**
     * Discount in Rand for a given subtotal — never more than the subtotal
     * itself (a fixed-amount code on a small cart can't produce a negative
     * total). Callers must still check isLive()/appliesToProducts() first;
     * this only does the arithmetic.
     */
    public function discountFor(float $subtotal): float
    {
        $raw = $this->discount_type === 'percentage'
            ? $subtotal * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        return round(min(max($raw, 0), $subtotal), 2);
    }

    public function getStatusLabelAttribute(): string
    {
        if (!$this->is_active) return 'Inactive';
        if ($this->max_uses && $this->used_count >= $this->max_uses) return 'Exhausted';
        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) return 'Scheduled';
        if ($this->valid_until && $now->gt($this->valid_until)) return 'Expired';
        return 'Live';
    }
}
