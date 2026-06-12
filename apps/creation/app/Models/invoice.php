<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory;
      protected $casts = [
        'due_date' => 'date',
    ];
    protected $fillable = [
        'company_id',
        'client_id',
        'invoice_number',
        'status',
        'total',
        'vat_total',
        'due_date',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);    
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function items()
    {
        return $this->hasMany(Invoice_items::class);    }
    public function payments()
    {
        return $this->hasMany(Payment::class);}

}
