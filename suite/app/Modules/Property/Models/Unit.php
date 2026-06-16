<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'property_id', 'unit_number', 'type', 'floor',
        'bedrooms', 'bathrooms', 'size_sqm', 'monthly_rent',
        'deposit_amount', 'status', 'notes',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease()
    {
        return $this->hasOne(Lease::class)->where('status', 'active');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function rentPayments()
    {
        return $this->hasMany(RentPayment::class);
    }

    public function isVacant(): bool
    {
        return $this->status === 'vacant';
    }
}
