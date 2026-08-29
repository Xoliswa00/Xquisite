<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class MaintenanceQuote extends Model
{
    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id', 'maintenance_request_id', 'contractor_id', 'amount', 'notes',
        'status', 'submitted_at', 'decided_at', 'decided_by', 'completed_at',
        'paid_at', 'payment_reference',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'submitted_at' => 'datetime',
        'decided_at'   => 'datetime',
        'completed_at' => 'datetime',
        'paid_at'      => 'datetime',
    ];

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function contractor()
    {
        return $this->belongsTo(Contractor::class);
    }

    public function decidedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'decided_by');
    }
}
