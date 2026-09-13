<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\OutreachCampaign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OutreachCampaignTest extends TestCase
{
    use RefreshDatabase;

    private function application(OutreachCampaign $campaign, array $overrides = []): FoundingTwentyApplication
    {
        return FoundingTwentyApplication::create(array_merge([
            'outreach_campaign_id' => $campaign->id,
            'business_name' => 'Test Salon',
            'owner_name' => 'Test Owner',
            'phone' => '082123' . rand(1000, 9999),
            'business_type' => 'salon',
            'preferred_contact_method' => 'whatsapp',
            'pain_forgotten_appointments' => 1, 'pain_late_cancellations' => 1, 'pain_no_shows' => 1,
            'pain_double_bookings' => 1, 'pain_booking_enquiry_time' => 1, 'pain_staff_availability' => 1,
            'pain_tracking_balances' => 1, 'pain_revenue_visibility' => 1, 'pain_customer_data_organisation' => 1,
            'value_rating' => 1,
            'status' => 'pending',
        ], $overrides));
    }

    public function test_industry_breakdown_groups_by_business_type_with_correct_counts(): void
    {
        $campaign = OutreachCampaign::create(['name' => 'Test Wave', 'status' => 'active']);

        $this->application($campaign, ['business_type' => 'salon', 'status' => 'converted', 'tier' => 'high']);
        $this->application($campaign, ['business_type' => 'salon', 'status' => 'selected']);
        $this->application($campaign, ['business_type' => 'salon', 'status' => 'pending']);
        $this->application($campaign, ['business_type' => 'beauty', 'status' => 'rejected']);

        $campaign->load('applications');
        $breakdown = $campaign->industryBreakdown();

        $this->assertSame(3, $breakdown['salon']['applied']);
        $this->assertSame(2, $breakdown['salon']['selected']); // selected + converted both count
        $this->assertSame(1, $breakdown['salon']['converted']);
        $this->assertSame(1, $breakdown['salon']['high_tier']);

        $this->assertSame(1, $breakdown['beauty']['applied']);
        $this->assertSame(0, $breakdown['beauty']['selected']);
    }

    public function test_applications_not_tagged_to_a_campaign_are_unaffected(): void
    {
        $campaign = OutreachCampaign::create(['name' => 'Test Wave', 'status' => 'active']);

        FoundingTwentyApplication::create([
            'business_name' => 'Untagged Salon', 'owner_name' => 'X', 'phone' => '0821239999',
            'business_type' => 'salon', 'preferred_contact_method' => 'whatsapp',
            'pain_forgotten_appointments' => 1, 'pain_late_cancellations' => 1, 'pain_no_shows' => 1,
            'pain_double_bookings' => 1, 'pain_booking_enquiry_time' => 1, 'pain_staff_availability' => 1,
            'pain_tracking_balances' => 1, 'pain_revenue_visibility' => 1, 'pain_customer_data_organisation' => 1,
            'value_rating' => 1,
        ]);

        $campaign->load('applications');

        $this->assertCount(0, $campaign->applications);
    }
}
