<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'client_id',
        'product_id',
        'status',
        'start_date',
        'end_date',
        'frequency',
        'next_invoice_date',
        'auto_renew',
    ];

    protected $casts = [
        'start_date'       => 'date',
        'end_date'         => 'date',
        'next_invoice_date' => 'date',
        'auto_renew'       => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
