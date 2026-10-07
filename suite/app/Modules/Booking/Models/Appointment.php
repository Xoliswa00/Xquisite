<?php

namespace App\Modules\Booking\Models;

use App\Support\PrivateFile;
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
        'inspiration_notes',
        'look_saved_at',
        'look_removed_at',
        'look_showcase_at',
        'rebook_reminded_at',
        'quote_status',
        'quoted_price',
        'quoted_duration_minutes',
        'quote_note',
        'quote_sent_at',
        'quote_responded_at',
        'terms_accepted_at',
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
        'terms_accepted_at' => 'datetime',
        'look_saved_at'    => 'datetime',
        'look_removed_at'  => 'datetime',
        'look_showcase_at' => 'datetime',
        'rebook_reminded_at' => 'datetime',
        'quoted_price'     => 'decimal:2',
        'quoted_duration_minutes' => 'integer',
        'quote_sent_at'    => 'datetime',
        'quote_responded_at' => 'datetime',
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

    /** Short-lived signed link to the customer's proof of payment (private file). */
    public function paymentProofUrl(): ?string
    {
        return $this->payment_proof_path ? PrivateFile::url('payment-proof', $this->id) : null;
    }

    public function isTentative(): bool
    {
        return $this->status === 'tentative';
    }

    /**
     * The business. Email templates already read $appointment->tenant->name,
     * which silently fell back to the app name while this relation was missing.
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /** A quote from the client's photos is outstanding (requested or sent, not answered). */
    public function quoteIsOpen(): bool
    {
        return in_array($this->quote_status, ['requested', 'sent'], true);
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
                ->withPivot(['duration_minutes', 'price_at_booking', 'quantity', 'sort_order'])
                ->withTimestamps()
                ->orderByPivot('sort_order');
}

public function services(): BelongsToMany
{
    return $this->belongsToMany(Service::class, 'appointment_services')
                ->withPivot(['duration_minutes', 'price_at_booking', 'quantity', 'sort_order'])
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

    /** Customer "this is the look I want" photos (private disk, see the model). */
    public function inspirationPhotos()
    {
        return $this->lookPhotos()->where('kind', AppointmentLookPhoto::KIND_INSPIRATION);
    }

    /** Staff "how it turned out" photos, added when saving the client's look. */
    public function resultPhotos()
    {
        return $this->lookPhotos()->where('kind', AppointmentLookPhoto::KIND_RESULT);
    }

    /** Every look photo, both kinds. */
    public function lookPhotos()
    {
        return $this->hasMany(AppointmentLookPhoto::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Look photos in display/copy order: staff after-photos first (that's the
     * look they liked), then the client's own inspiration. One place, so the
     * confirm preview, My Bookings and copyLook() can't disagree.
     */
    public function orderedLookPhotos(): \Illuminate\Support\Collection
    {
        return $this->lookPhotos
            ->sortBy(fn ($p) => [$p->isResult() ? 0 : 1, $p->sort_order, $p->id])
            ->values();
    }

    public function isLookSaved(): bool
    {
        return $this->look_saved_at !== null;
    }

    /** The client removed this look; staff can't save it again. */
    public function lookWasRemovedByClient(): bool
    {
        return $this->look_removed_at !== null;
    }

    /** Whether looks apply to this booking at all (some service takes inspiration, or photos already exist). */
    public function usesLooks(): bool
    {
        return $this->services->contains(fn ($s) => $s->accepts_inspiration_photos)
            || $this->lookPhotos->isNotEmpty()
            || (bool) $this->inspiration_notes;
    }

    /** Staff can add after photos / save the look once the appointment has happened. */
    public function lookCanBeRecorded(): bool
    {
        return ! in_array($this->status, ['cancelled', 'no_show'], true)
            && ($this->status === 'completed' || $this->scheduled_at?->isPast());
    }

    /** Saved looks only. */
    public function scopeSavedLooks($query)
    {
        return $query->whereNotNull('look_saved_at')->orderByDesc('scheduled_at');
    }

    /** Whether the customer can still add or remove inspiration photos. */
    public function inspirationIsEditable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed'], true)
            && $this->scheduled_at?->isFuture();
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
