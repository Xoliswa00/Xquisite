<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;
use App\Modules\POS\Models\Product;

class ServiceProduct extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'service_id',
        'product_id',
        'name',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
