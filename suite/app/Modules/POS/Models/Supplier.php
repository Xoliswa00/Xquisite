<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class Supplier extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'contact_person',
        'website',
        'address',
        'payment_terms',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function getActiveOrdersCountAttribute(): int
    {
        return $this->purchaseOrders()
            ->whereIn('status', [PurchaseOrder::STATUS_DRAFT, PurchaseOrder::STATUS_SENT, PurchaseOrder::STATUS_PARTIAL])
            ->count();
    }
}
