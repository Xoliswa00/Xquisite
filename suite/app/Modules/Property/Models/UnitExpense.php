<?php

namespace App\Modules\Property\Models;

use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class UnitExpense extends Model
{
    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id', 'unit_id', 'maintenance_request_id',
        'category', 'description', 'amount', 'date', 'notes',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function maintenanceRequest()
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }
}
