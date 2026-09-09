<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoundingTwentyCheckin extends Model
{
    use Auditable;

    protected $fillable = [
        'founding_twenty_application_id', 'checkin_type',
        'monthly_appointments', 'no_shows_per_month', 'avg_appointment_value',
        'hours_booking_admin', 'hours_availability_messages', 'hours_manual_reminders',
        'value_rating', 'continuation_likelihood', 'continuation_driver', 'churn_driver',
        'biggest_change', 'would_recommend',
        'completed_at',
    ];

    protected $casts = [
        'would_recommend' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(FoundingTwentyApplication::class, 'founding_twenty_application_id');
    }

    public function isComplete(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * The 90-day mark is when the free period actually ends, so it's the only
     * check-in that asks about continuing/cancelling at R200/month — asking
     * that any earlier is speculation the business can't yet answer from experience.
     */
    public function isFinalCheckin(): bool
    {
        return $this->checkin_type === '90_day';
    }

    public function label(): string
    {
        return match ($this->checkin_type) {
            '30_day' => '30-day check-in',
            '60_day' => '60-day check-in',
            '90_day' => '90-day check-in',
            default => $this->checkin_type,
        };
    }

    public function checkinToken(): string
    {
        return hash_hmac('sha256', $this->id . '|' . $this->founding_twenty_application_id, config('app.key'));
    }
}
