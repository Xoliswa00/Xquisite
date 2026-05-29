<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id', 'property_id', 'unit_id', 'lease_id', 'renter_id',
        'title', 'description', 'priority', 'status',
        'assigned_to', 'resolution_notes', 'resolved_at',
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
