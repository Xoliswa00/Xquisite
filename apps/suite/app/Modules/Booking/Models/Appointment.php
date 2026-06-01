<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use App\Modules\POS\Models\Sale;


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
        'headcount',
        'venue',
        'event_type',
        'dietary_notes',
        'theme_notes',
        'setup_at',
        'breakdown_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'setup_at'     => 'datetime',
        'breakdown_at' => 'datetime',
    ];

    public function isEventBooking(): bool
    {
        return ! empty($this->headcount) || ! empty($this->venue) || ! empty($this->event_type);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class)->withDefault(['name' => 'Unassigned']);
    }

    public function isUnassigned(): bool
    {
        return $this->staff_id === null;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    public function reminders()
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>=', now())->orderBy('scheduled_at');
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'confirmed'  => 'emerald',
            'completed'  => 'blue',
            'cancelled'  => 'red',
            'no_show'    => 'gray',
            default      => 'yellow', // pending
        };
    }
}
