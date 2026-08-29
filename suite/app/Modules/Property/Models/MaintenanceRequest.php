<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id', 'property_id', 'unit_id', 'lease_id', 'renter_id',
        'title', 'description', 'priority', 'status',
        'assigned_to', 'contractor_id', 'resolution_notes', 'resolved_at',
    ];

    protected $casts = ['resolved_at' => 'datetime'];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function renter()
    {
        return $this->belongsTo(Renter::class);
    }

    public function photos()
    {
        return $this->hasMany(MaintenancePhoto::class);
    }

    /** The contractor awarded this job — set automatically when one of their quotes is approved. */
    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    /** Contractors invited to submit a quote on this job (may be more than one, competing). */
    public function invitedContractors()
    {
        return $this->belongsToMany(Contractor::class, 'maintenance_request_contractor')->withTimestamps();
    }

    public function quotes()
    {
        return $this->hasMany(MaintenanceQuote::class);
    }

    public function priorityColor(): string
    {
        return match($this->priority) {
            'urgent' => 'red',
            'high'   => 'orange',
            'medium' => 'yellow',
            default  => 'slate',
        };
    }
}
