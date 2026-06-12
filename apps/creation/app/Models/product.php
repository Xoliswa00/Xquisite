<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'price',
        'billing_type',
        'vat_rate',
        'product_group_id',
        'product_category_id',
        'service_category_id',
        'duration_minutes',
        'is_active',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
      public function quoteItems()
    {
        return $this->hasMany(quote_items::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(invoice_items::class);
    }
    public function items()
    {
        return $this->hasMany(product_items::class);
    }
  
    public function group()
    {
        return $this->belongsTo(product_group::class, 'product_group_id');
    }
    public function category()
    {
        return $this->belongsTo(category::class, 'product_category_id');
    }
    public function product_category()
    {
        return $this->belongsTo(product_category::class, 'product_category_id');
    }
    public function product_group()
    {
        return $this->belongsTo(product_group::class, 'product_group_id');
    }
    public function pricing()
    {
        return $this->hasMany(product_price::class, 'product_id');
    }

    public function prices()
    {
        return $this->hasMany(product_price::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function getDurationLabelAttribute(): ?string
    {
        if (! $this->duration_minutes) return null;
        $h = intdiv($this->duration_minutes, 60);
        $m = $this->duration_minutes % 60;
        if ($h > 0 && $m > 0) return "{$h}h {$m}min";
        if ($h > 0) return "{$h}h";
        return "{$m}min";
    }
    


    


}
