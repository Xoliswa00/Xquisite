<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use Auditable;

    protected $fillable = [
        'code', 'type', 'value', 'max_redemptions', 'times_redeemed',
        'expires_at', 'is_active', 'source', 'notes', 'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'expires_at' => 'date',
        'is_active' => 'boolean',
    ];

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromoCodeRedemption::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExhausted(): bool
    {
        return $this->max_redemptions !== null && $this->times_redeemed >= $this->max_redemptions;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isRedeemable(): bool
    {
        // max_redemptions is a soft cap for visibility (e.g. "Founding 20"), not a
        // hard stop — going over it should be flagged, not silently blocked.
        return $this->is_active && !$this->isExpired();
    }

    public function describeDiscount(): string
    {
        return match ($this->type) {
            'free_months' => (int) $this->value . ' month' . ((int) $this->value === 1 ? '' : 's') . ' free',
            'percentage' => number_format($this->value, 0) . '% off',
            'fixed_amount' => 'R' . number_format($this->value, 2) . ' off',
        };
    }
}
