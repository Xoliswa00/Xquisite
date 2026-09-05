<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\FoundingTwentyCheckin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundingTwentyCheckinTest extends TestCase
{
    use RefreshDatabase;

    private function application(): FoundingTwentyApplication
    {
        return FoundingTwentyApplication::create([
            'business_name' => 'Test Salon',
            'owner_name' => 'Test Owner',
            'phone' => '0821234567',
            'business_type' => 'salon',
            'preferred_contact_method' => 'whatsapp',
            'pain_forgotten_appointments' => 1, 'pain_late_cancellations' => 1, 'pain_no_shows' => 1,
            'pain_double_bookings' => 1, 'pain_booking_enquiry_time' => 1, 'pain_staff_availability' => 1,
            'pain_tracking_balances' => 1, 'pain_revenue_visibility' => 1, 'pain_customer_data_organisation' => 1,
            'value_rating' => 1,
        ]);
    }

    public function test_checkin_is_not_complete_until_completed_at_is_set(): void
    {
        $checkin = $this->application()->checkins()->create(['checkin_type' => '30_day']);

        $this->assertFalse($checkin->isComplete());

        $checkin->update(['completed_at' => now()]);

        $this->assertTrue($checkin->fresh()->isComplete());
    }

    public function test_label_describes_each_checkin_type(): void
    {
        $application = $this->application();

        $this->assertSame('30-day check-in', $application->checkins()->create(['checkin_type' => '30_day'])->label());
        $this->assertSame('60-day check-in', $application->checkins()->create(['checkin_type' => '60_day'])->label());
        $this->assertSame('90-day check-in', $application->checkins()->create(['checkin_type' => '90_day'])->label());
    }

    public function test_two_checkins_have_different_tokens(): void
    {
        $application = $this->application();
        $checkinA = $application->checkins()->create(['checkin_type' => '30_day']);
        $checkinB = $application->checkins()->create(['checkin_type' => '60_day']);

        $this->assertNotSame($checkinA->checkinToken(), $checkinB->checkinToken());
    }

    public function test_same_type_cannot_be_issued_twice_for_one_application(): void
    {
        $application = $this->application();
        $application->checkins()->create(['checkin_type' => '30_day']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $application->checkins()->create(['checkin_type' => '30_day']);
    }
}
