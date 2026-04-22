<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;


class Appointment extends Model
{
    //
    use HasTenant, Auditable;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'staff_id',
        'service_id',
        'scheduled_at',
        'duration_minutes',
        'status',
        'pos_order_id',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
