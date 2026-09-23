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

    // ── Applicant experience ─────────────────────────────────────────────────

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'Test Salon',
            'owner_name' => 'Test Owner',
            'phone' => '0821234567',
            'business_type' => 'salon',
            'preferred_contact_method' => 'whatsapp',
            'pain_forgotten_appointments' => 3, 'pain_late_cancellations' => 3, 'pain_no_shows' => 3,
            'pain_double_bookings' => 3, 'pain_booking_enquiry_time' => 3, 'pain_staff_availability' => 3,
            'pain_tracking_balances' => 3, 'pain_revenue_visibility' => 3, 'pain_customer_data_organisation' => 3,
            'privacy_consent' => '1',
        ], $overrides);
    }

    public function test_explainer_is_upfront_about_the_deposit_and_does_not_say_launching_soon(): void
    {
        $response = $this->get(route('founding-20.show'));

        $response->assertOk();
        $response->assertSee('How it works');
        $response->assertSee('fully refundable R100 deposit');
        $response->assertSee('Applications are open');
        $response->assertDontSee('Launching soon');
    }

    public function test_questionnaire_uses_a_phone_keyboard_and_shows_progress(): void
    {
        $response = $this->get(route('founding-twenty.show'));

        $response->assertSee('type="tel"', false);
        $response->assertSee('id="progress-bar"', false);
        $response->assertSee('Nothing is charged when you apply.');
    }

    public function test_missing_rating_error_names_the_question_in_plain_language(): void
    {
        $payload = $this->validPayload();
        unset($payload['pain_no_shows']);

        $response = $this->from(route('founding-twenty.show'))->post(route('founding-twenty.store'), $payload);

        $response->assertRedirect(route('founding-twenty.show'));
        $this->assertSame(
            'Please rate "Clients not showing up at all" from 1 to 5 (Section 3).',
            session('errors')->first('pain_no_shows')
        );
    }

    public function test_email_is_required_when_email_is_the_preferred_contact_method(): void
    {
        $response = $this->from(route('founding-twenty.show'))->post(
            route('founding-twenty.store'),
            $this->validPayload(['preferred_contact_method' => 'email'])
        );

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('since you chose email', session('errors')->first('email'));
    }

    public function test_email_stays_optional_for_whatsapp_and_call(): void
    {
        foreach (['whatsapp', 'call'] as $method) {
            $this->post(route('founding-twenty.store'), $this->validPayload(['preferred_contact_method' => $method]))
                ->assertSessionDoesntHaveErrors();
        }
    }

    public function test_ticked_checkboxes_survive_a_validation_error(): void
    {
        $response = $this->followingRedirects()->from(route('founding-twenty.show'))->post(
            route('founding-twenty.store'),
            $this->validPayload([
                'phone' => '123',
                'booking_methods' => ['whatsapp', 'website'],
                'priority_features' => ['payments'],
            ])
        );

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/name="booking_methods\[\]" value="whatsapp"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/name="booking_methods\[\]" value="website"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/name="priority_features\[\]" value="payments"\s+checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="booking_methods\[\]" value="phone"\s+checked/', $html);
    }

    public function test_thank_you_page_explains_what_happens_next_and_lets_them_ask_a_question(): void
    {
        $response = $this->get(route('founding-twenty.thanks'));

        $response->assertOk();
        $response->assertSee('What happens next');
        $response->assertSee('fully refundable R100 deposit');
        $response->assertSee(route('founding-20.show') . '#questions', false);
    }

    public function test_asking_a_question_returns_the_visitor_to_the_question_form(): void
    {
        $response = $this->from(route('founding-20.show'))->post(route('founding-20.questions.store'), [
            'question' => 'Is there a contract?',
        ]);

        $response->assertRedirect(route('founding-20.show') . '#questions');
    }

    public function test_homepage_links_to_founding_20_for_phone_visitors(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('See how it works');
        $response->assertSee('href="' . route('founding-20.show') . '"', false);
    }
}
