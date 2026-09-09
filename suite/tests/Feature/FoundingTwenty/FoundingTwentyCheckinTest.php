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

    public function test_only_the_90_day_checkin_is_the_final_checkin(): void
    {
        $application = $this->application();

        $this->assertFalse($application->checkins()->create(['checkin_type' => '30_day'])->isFinalCheckin());
        $this->assertFalse($application->checkins()->create(['checkin_type' => '60_day'])->isFinalCheckin());
        $this->assertTrue($application->checkins()->create(['checkin_type' => '90_day'])->isFinalCheckin());
    }

    public function test_public_form_no_longer_asks_continuation_questions_at_application_time(): void
    {
        $response = $this->get(route('founding-twenty.show'));

        $response->assertOk();
        $response->assertDontSee('What would make you cancel?');
        $response->assertDontSee('What would make you continue?');
    }

    public function test_application_can_be_submitted_without_continuation_fields(): void
    {
        $response = $this->post(route('founding-twenty.store'), [
            'business_name' => 'Test Salon',
            'owner_name' => 'Test Owner',
            'phone' => '0821234567',
            'business_type' => 'salon',
            'preferred_contact_method' => 'whatsapp',
            'pain_forgotten_appointments' => 3, 'pain_late_cancellations' => 3, 'pain_no_shows' => 3,
            'pain_double_bookings' => 3, 'pain_booking_enquiry_time' => 3, 'pain_staff_availability' => 3,
            'pain_tracking_balances' => 3, 'pain_revenue_visibility' => 3, 'pain_customer_data_organisation' => 3,
            'value_rating' => 4,
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect(route('founding-twenty.thanks'));
        $response->assertSessionDoesntHaveErrors();
        $application = FoundingTwentyApplication::first();
        $this->assertNotNull($application);
        $this->assertNull($application->continuation_likelihood);
        $this->assertNotNull($application->score);
    }

    public function test_90_day_checkin_page_asks_what_would_make_them_continue_or_cancel(): void
    {
        $checkin = $this->application()->checkins()->create(['checkin_type' => '90_day']);

        $response = $this->get(route('founding-twenty.checkin.show', [$checkin, $checkin->checkinToken()]));

        $response->assertOk();
        $response->assertSee('What would make you continue?');
        $response->assertSee('What would make you cancel?');
    }

    public function test_30_day_checkin_page_does_not_ask_what_would_make_them_continue_or_cancel(): void
    {
        $checkin = $this->application()->checkins()->create(['checkin_type' => '30_day']);

        $response = $this->get(route('founding-twenty.checkin.show', [$checkin, $checkin->checkinToken()]));

        $response->assertOk();
        $response->assertDontSee('What would make you continue?');
        $response->assertDontSee('What would make you cancel?');
    }

    public function test_submitting_the_90_day_checkin_saves_continuation_and_churn_drivers(): void
    {
        $checkin = $this->application()->checkins()->create(['checkin_type' => '90_day']);

        $response = $this->post(route('founding-twenty.checkin.store', [$checkin, $checkin->checkinToken()]), [
            'value_rating' => 4,
            'continuation_likelihood' => 'likely',
            'continuation_driver' => 'Keep saving me time on reminders.',
            'churn_driver' => 'If the price went up.',
        ]);

        $response->assertRedirect();
        $checkin->refresh();
        $this->assertSame('Keep saving me time on reminders.', $checkin->continuation_driver);
        $this->assertSame('If the price went up.', $checkin->churn_driver);
        $this->assertTrue($checkin->isComplete());
    }
}
