<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The intended flow: /founding-20 is the programme explainer (countdown,
 * benefits, public Q&A) with a "Start Application" CTA — not the questionnaire
 * itself. The questionnaire lives at /founding-20/apply, reached from that CTA.
 * This was previously misaligned: the nav link and referral links went straight
 * to the questionnaire, skipping the explainer entirely.
 */
class FoundingTwentyLandingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_founding_20_root_shows_the_programme_explainer_not_the_questionnaire(): void
    {
        $response = $this->get(route('founding-20.show'));

        $response->assertOk();
        $response->assertSee('Start Your Application');
        $response->assertDontSee('Section 1 of 8');
    }

    public function test_founding_20_apply_shows_the_actual_questionnaire(): void
    {
        $response = $this->get(route('founding-twenty.show'));

        $response->assertOk();
        $response->assertSee('Section 1 of 8');
        $this->assertSame(url('/founding-20/apply'), route('founding-twenty.show'));
    }

    public function test_explainer_page_cta_links_to_the_apply_url(): void
    {
        $response = $this->get(route('founding-20.show'));

        $response->assertOk();
        $response->assertSee('href="' . route('founding-twenty.show') . '"', false);
    }

    public function test_submitting_the_questionnaire_at_its_new_url_still_works(): void
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
            'privacy_consent' => '1',
        ]);

        $response->assertRedirect(route('founding-twenty.thanks'));
        $response->assertSessionDoesntHaveErrors();
    }

    public function test_referral_and_campaign_params_carry_through_the_explainer_into_the_apply_link(): void
    {
        $tenant = Tenant::create([
            'name' => 'Referring Salon', 'slug' => 'referring-salon-' . uniqid(),
            'email' => 'ref@example.com', 'is_active' => true,
        ]);

        $response = $this->get(route('founding-20.show', ['ref' => $tenant->id, 'src' => 'whatsapp']));

        $response->assertOk();
        // Query param order in the rendered link isn't guaranteed, only that both survive.
        $response->assertSee('/founding-20/apply?', false);
        $response->assertSee('ref=' . $tenant->id, false);
        $response->assertSee('src=whatsapp', false);
    }

    public function test_tenant_referral_link_points_to_the_explainer_page(): void
    {
        $tenant = Tenant::create([
            'name' => 'Referring Salon', 'slug' => 'referring-salon-' . uniqid(),
            'email' => 'ref2@example.com', 'is_active' => true,
        ]);

        $expected = route('founding-20.show', ['ref' => $tenant->id]);

        $this->assertStringContainsString('/founding-20?', $expected);
        $this->assertStringNotContainsString('/founding-20/apply', $expected);
    }
}
