<?php

namespace App\Modules\Booking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasTenant;
use App\Models\Traits\Auditable;
use App\Modules\POS\Models\Sale;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;



class Appointment extends Model
{
    //
    use HasTenant, Auditable, SoftDeletes;

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
        'combo_id',
        'combo_price',
        'promo_code',
        'promo_discount',
        'actual_duration_minutes',
        'payment_proof_path',
        'payment_proof_name',
        'headcount',
        'venue',
        'event_type',
        'dietary_notes',
        'theme_notes',
        'setup_at',
        'breakdown_at',
    ];

    protected $casts = [
        'scheduled_at'     => 'datetime',
        'setup_at'         => 'datetime',
        'breakdown_at'     => 'datetime',
        'duration_minutes' => 'integer',
        'combo_price'             => 'decimal:2',
        'promo_discount'          => 'decimal:2',
        'actual_duration_minutes' => 'integer',
    ];

    public function isEventBooking(): bool
    {
        return ! empty($this->headcount) || ! empty($this->venue) || ! empty($this->event_type);}
    public function paymentPlan()
    {
        return $this->morphOne(\App\Models\PaymentPlan::class, 'plannable');
    }

    public function isTentative(): bool
    {
        return $this->status === 'tentative';
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

  // Instead of belongsTo(Service::class)
public function service(): BelongsToMany
{
    return $this->belongsToMany(Service::class, 'appointment_services')
                ->withPivot(['duration_minutes', 'price_at_booking', 'sort_order'])
                ->withTimestamps()
                ->orderByPivot('sort_order');
}

public function services(): BelongsToMany
{
    return $this->belongsToMany(Service::class, 'appointment_services')
                ->withPivot(['duration_minutes', 'price_at_booking', 'sort_order'])
                ->withTimestamps()
                ->orderByPivot('sort_order');
}

public function totalDuration(): int
{
    return $this->services->sum(fn($s) => $s->pivot->duration_minutes ?? $s->duration_minutes);
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
