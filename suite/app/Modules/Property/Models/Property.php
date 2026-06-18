<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'name', 'address_line_1', 'address_line_2',
        'city', 'province', 'postal_code', 'country', 'type',
        'description', 'owner_name', 'owner_email', 'owner_phone', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function units()
    {
        return $this->hasMany(Unit::class);
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
