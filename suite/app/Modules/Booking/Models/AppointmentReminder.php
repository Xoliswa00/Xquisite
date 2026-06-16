<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;

class AppointmentReminder extends Model
{
    use HasTenant;

    protected $fillable = [
        'tenant_id',
        'appointment_id',
        'type',
        'scheduled_at',
        'sent_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at'      => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
