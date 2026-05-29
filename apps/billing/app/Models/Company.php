<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory;
    protected $fillable = [
        'name',
        'address_line_1',
        'address_line_2',
        'city',
        'province',
        'postal_code',
        'country',
        'email',
        'phone',    
        'website',
        'entity_type',
        'legal_name',
        'registration_number',
        'tax_number',
        'vat_number',
        'currency',
        'vat_registered',
        'default_vat_rate',
        'status',
        'slug',
        'domain',
        'logo_path',
        'timezone',
        'billing_email',
        'plan',
        'subscription_plan',
        'subscription_status',
        'subscription_renewal_date',
        'platform_owner_id',
        'is_platform_company'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'platform_owner_id');
    }

    public function invitations()
    {
        return $this->hasMany(CompanyInvitation::class);
    }

    public function domains()
    {
        return $this->hasMany(CompanyDomain::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'company_users')->withPivot('role')->withTimestamps();
    }
    public function clients()
    {
        return $this->hasMany(Client::class, 'company_id');
    }
    public function products()
    {
        return $this->hasMany(Product::class, 'company_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'company_id');
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'company_id');
    }
}
