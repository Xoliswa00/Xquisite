<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class client extends Model
{
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
        'country',
            'contact_person',
            'website',
            'tax_number',
            'vat_number',
            'user_id'
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);      }

        public function user(){
            return $this->belongsTo(User::class);
        }


                public function isBillingReady()
        {
            return $this->name
            && $this->email
            && $this->phone
                && $this->billing_address
                && $this->vat_number
                && $this->tax_number
                && $this->contact_person;
        }

        


   
    
    
    }
