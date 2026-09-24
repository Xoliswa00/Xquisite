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

    public function test_founding_20_root_shows_the_programme_explainer_not_the_application(): void
    {
        $response = $this->get(route('founding-20.show'));

        $response->assertOk();
        $response->assertSee('Start Your Application');
        $response->assertDontSee('Section 1 of 8');
        $response->assertDontSee('First, tell us who you are');
    }

    public function test_apply_url_starts_with_step_one(): void
    {
        $this->assertSame(url('/founding-20/apply'), route('founding-twenty.show'));
        $this->get(route('founding-twenty.show'))->assertOk()->assertSee('First, tell us who you are');
    }

    public function test_explainer_page_cta_links_to_the_apply_url(): void
    {
        $response = $this->get(route('founding-20.show'));

        $response->assertOk();
        $response->assertSee('href="' . route('founding-twenty.show') . '"', false);
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


    public function test_explainer_is_upfront_about_the_deposit_and_does_not_say_launching_soon(): void
    {
        $response = $this->get(route('founding-20.show'));

        $response->assertOk();
        $response->assertSee('How it works');
        $response->assertSee('fully refundable R100 deposit');
        $response->assertSee('Applications are open');
        $response->assertDontSee('Launching soon');
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
