<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FoundingTwentyApplication extends Model
{
    use Auditable;

    protected $fillable = [
        'business_name', 'owner_name', 'email', 'phone', 'business_type', 'business_type_other',
        'location', 'preferred_contact_method', 'best_contact_time',
        'years_operating', 'staff_count', 'locations_count', 'monthly_customers', 'monthly_appointments',
        'booking_methods', 'appointment_management_methods', 'customer_data_methods',
        'payment_tracking_methods', 'balance_tracking_methods', 'card_payment_device',
        'pain_forgotten_appointments', 'pain_late_cancellations', 'pain_no_shows', 'pain_double_bookings',
        'pain_booking_enquiry_time', 'pain_staff_availability', 'pain_tracking_balances',
        'pain_revenue_visibility', 'pain_customer_data_organisation',
        'no_shows_per_month', 'avg_appointment_value',
        'hours_booking_admin', 'hours_availability_messages', 'hours_manual_reminders',
        'adoption_barriers', 'adoption_barrier_other', 'past_solution_frustration',
        'priority_features', 'top_priority_feature', 'automation_wishlist',
        'value_rating', 'value_open_text',
        'continuation_likelihood', 'continuation_driver', 'churn_driver',
        'wants_founding_twenty', 'willing_to_give_feedback',
        'score', 'tier', 'status', 'source', 'ip_address',
        'reviewed_by', 'reviewed_at', 'admin_notes',
        'privacy_consented_at',
        'deposit_amount', 'deposit_reference', 'deposit_pop_path',
        'deposit_submitted_at', 'deposit_confirmed_at', 'deposit_refunded_at',
        'tenant_id', 'first_value_milestone_at', 'first_value_milestone_note',
    ];

    protected $casts = [
        'booking_methods' => 'array',
        'appointment_management_methods' => 'array',
        'customer_data_methods' => 'array',
        'payment_tracking_methods' => 'array',
        'balance_tracking_methods' => 'array',
        'adoption_barriers' => 'array',
        'priority_features' => 'array',
        'wants_founding_twenty' => 'boolean',
        'willing_to_give_feedback' => 'boolean',
        'reviewed_at' => 'datetime',
        'privacy_consented_at' => 'datetime',
        'deposit_amount' => 'decimal:2',
        'deposit_submitted_at' => 'datetime',
        'deposit_confirmed_at' => 'datetime',
        'deposit_refunded_at' => 'datetime',
        'first_value_milestone_at' => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function promoCodeRedemption(): HasOne
    {
        return $this->hasOne(PromoCodeRedemption::class);
    }

    public function reservationToken(): string
    {
        return hash_hmac('sha256', $this->id . $this->phone, config('app.key'));
    }
}
