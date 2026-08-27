<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id', 'name', 'address_line_1', 'address_line_2',
        'city', 'province', 'postal_code', 'country', 'type',
        'description', 'owner_name', 'owner_email', 'owner_phone', 'owner_id_number', 'is_active',
        'annual_increase_percentage',
    ];

    protected $casts = [
        'is_active'                   => 'boolean',
        'annual_increase_percentage'  => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class)->latest();
    }

    public function coverImage()
    {
        return $this->hasOne(PropertyImage::class)->oldestOfMany();
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function vacantUnits()
    {
        return $this->hasMany(Unit::class)->where('status', 'vacant');
    }

    public function occupiedUnits()
    {
        return $this->hasMany(Unit::class)->where('status', 'occupied');
    }
}
