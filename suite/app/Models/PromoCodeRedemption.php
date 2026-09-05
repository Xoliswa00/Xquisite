<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCodeRedemption extends Model
{
    use Auditable;

    protected $fillable = [
        'promo_code_id', 'tenant_id', 'founding_twenty_application_id', 'redeemed_by',
        'discount_type', 'discount_value', 'financial_value', 'notes', 'redeemed_at',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'financial_value' => 'decimal:2',
        'redeemed_at' => 'datetime',
    ];

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function foundingTwentyApplication(): BelongsTo
    {
        return $this->belongsTo(FoundingTwentyApplication::class);
    }

    public function redeemer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }
}
