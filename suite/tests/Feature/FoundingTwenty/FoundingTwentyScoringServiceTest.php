<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Services\FoundingTwentyScoringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundingTwentyScoringServiceTest extends TestCase
{
    use RefreshDatabase;

    private function application(array $overrides = []): FoundingTwentyApplication
    {
        return FoundingTwentyApplication::create(array_merge([
            'business_name' => 'Test Salon',
            'owner_name' => 'Test Owner',
            'phone' => '0821234567',
            'business_type' => 'salon',
            'preferred_contact_method' => 'whatsapp',
            'pain_forgotten_appointments' => 1,
            'pain_late_cancellations' => 1,
            'pain_no_shows' => 1,
            'pain_double_bookings' => 1,
            'pain_booking_enquiry_time' => 1,
            'pain_staff_availability' => 1,
            'pain_tracking_balances' => 1,
            'pain_revenue_visibility' => 1,
            'pain_customer_data_organisation' => 1,
            'value_rating' => 1,
        ], $overrides));
    }

    public function test_minimal_answers_score_low(): void
    {
        $application = $this->application();

        $result = app(FoundingTwentyScoringService::class)->score($application);

        $this->assertSame(14, $result['score']);
        $this->assertSame('low', $result['tier']);
    }

    public function test_moderate_pain_and_volume_scores_good(): void
    {
        $application = $this->application([
            'monthly_appointments' => '151-300',
            'pain_forgotten_appointments' => 4, 'pain_late_cancellations' => 4, 'pain_no_shows' => 4,
            'pain_double_bookings' => 4, 'pain_booking_enquiry_time' => 4, 'pain_staff_availability' => 4,
            'pain_tracking_balances' => 4, 'pain_revenue_visibility' => 4, 'pain_customer_data_organisation' => 4,
            'no_shows_per_month' => '3-5',
            'hours_booking_admin' => '5-10',
            'hours_availability_messages' => '3-5',
            'hours_manual_reminders' => '3-5',
            'staff_count' => '2-5',
            'booking_methods' => ['whatsapp'],
            'appointment_management_methods' => ['whatsapp'],
            'payment_tracking_methods' => ['notebook'],
            'balance_tracking_methods' => ['notebook'],
            'value_rating' => 4,
            'continuation_likelihood' => 'likely',
            'willing_to_give_feedback' => true,
        ]);

        $result = app(FoundingTwentyScoringService::class)->score($application);

        // Hand-verified breakdown: 9 (volume) + 15 (pain) + 6 (no-shows) + 6.67 (admin time)
        // + 6 (staff) + 10 (payment problem, manual-only) + 2 (tech readiness, no digital signals)
        // + 7.5 (interest) + 8.75 (feedback willingness) = 70.92, rounds to 71.
        $this->assertSame(71, $result['score']);
        $this->assertSame('good', $result['tier']);
    }

    public function test_high_pain_and_volume_scores_high(): void
    {
        $application = $this->application([
            'monthly_appointments' => '300+',
            'pain_forgotten_appointments' => 5, 'pain_late_cancellations' => 5, 'pain_no_shows' => 5,
            'pain_double_bookings' => 5, 'pain_booking_enquiry_time' => 5, 'pain_staff_availability' => 5,
            'pain_tracking_balances' => 5, 'pain_revenue_visibility' => 5, 'pain_customer_data_organisation' => 5,
            'no_shows_per_month' => '10+',
            'hours_booking_admin' => '10+',
            'hours_availability_messages' => '10+',
            'hours_manual_reminders' => '10+',
            'staff_count' => '10+',
            'payment_tracking_methods' => ['notebook'],
            'balance_tracking_methods' => ['notebook'],
            'value_rating' => 5,
            'continuation_likelihood' => 'very_likely',
            'willing_to_give_feedback' => true,
        ]);

        $result = app(FoundingTwentyScoringService::class)->score($application);

        $this->assertSame(92, $result['score']);
        $this->assertSame('high', $result['tier']);
    }

    public function test_score_never_exceeds_100_or_drops_below_0(): void
    {
        $application = $this->application([
            'monthly_appointments' => '300+',
            'pain_forgotten_appointments' => 5, 'pain_late_cancellations' => 5, 'pain_no_shows' => 5,
            'pain_double_bookings' => 5, 'pain_booking_enquiry_time' => 5, 'pain_staff_availability' => 5,
            'pain_tracking_balances' => 5, 'pain_revenue_visibility' => 5, 'pain_customer_data_organisation' => 5,
            'no_shows_per_month' => '10+',
            'hours_booking_admin' => '10+', 'hours_availability_messages' => '10+', 'hours_manual_reminders' => '10+',
            'staff_count' => '10+',
            'booking_methods' => ['website', 'booking_platform'],
            'appointment_management_methods' => ['google_calendar'],
            'payment_tracking_methods' => ['accounting_software', 'pos'],
            'balance_tracking_methods' => ['accounting_software'],
            'value_rating' => 5,
            'continuation_likelihood' => 'very_likely',
            'willing_to_give_feedback' => true,
        ]);

        $result = app(FoundingTwentyScoringService::class)->score($application);

        $this->assertGreaterThanOrEqual(0, $result['score']);
        $this->assertLessThanOrEqual(100, $result['score']);
    }
}
