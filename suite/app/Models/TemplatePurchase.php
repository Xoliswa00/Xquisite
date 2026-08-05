<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplatePurchase extends Model
{
    protected $fillable = [
        'reference',
        'tenant_id',
        'template_key',
        'amount',
        'status',
        'payfast_payment_id',
        'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class, 'template_key', 'key');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
