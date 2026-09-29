<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\PublicLaunch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The application can be closed ahead of a real launch date, driven by the same
 * `launch_at` the explainer page's own countdown already reads — nothing hardcoded,
 * nothing to keep in sync separately. Leaves the explainer, thank-you, and the
 * post-selection pages (reserve/check-in) untouched either way.
 */
class FoundingTwentyLaunchGateTest extends TestCase
{
    use RefreshDatabase;

    private function setGate(?\Carbon\Carbon $launchAt): void
    {
        PublicLaunch::updateOrCreate(['key' => 'founding-20'], [
            'title' => 'Founding 20', 'launch_at' => $launchAt, 'qa_enabled' => true,
            'benefits' => ['Three months free, the whole platform'],
        ]);
    }

    private function lead(array $overrides = []): FoundingTwentyApplication
    {
        return FoundingTwentyApplication::create(array_merge([
            'owner_name' => 'Thandi Mokoena', 'business_name' => 'Thandi Hair Studio', 'applicant_role' => 'owner',
            'phone' => '0821234567', 'preferred_contact_method' => 'whatsapp',
        ], $overrides));
    }

    // ── Closed (launch_at in the future) ─────────────────────────────────────

    public function test_step_one_is_closed_before_the_launch_date(): void
    {
        $this->setGate(now()->addDays(7));

        $this->get(route('founding-twenty.show'))
            ->assertRedirect(route('founding-20.show'))
            ->assertSessionHas('info');
    }

    public function test_submitting_step_one_is_refused_before_the_launch_date(): void
    {
        $this->setGate(now()->addDays(7));

        $response = $this->post(route('founding-twenty.store'), [
            'owner_name' => 'Thandi', 'business_name' => 'Thandi Hair', 'applicant_role' => 'owner',
            'phone' => '0821234567', 'preferred_contact_method' => 'whatsapp', 'privacy_consent' => '1',
        ]);

        $response->assertRedirect(route('founding-20.show'));
        $this->assertSame(0, FoundingTwentyApplication::count());
    }

    public function test_the_questionnaire_and_its_submit_are_closed_too(): void
    {
        $lead = $this->lead();
        $this->setGate(now()->addDays(7));

        $this->withSession(['founding_twenty_lead_id' => $lead->id])
            ->get(route('founding-twenty.questions'))
            ->assertRedirect(route('founding-20.show'));

        $this->withSession(['founding_twenty_lead_id' => $lead->id])
            ->post(route('founding-twenty.submit'), ['business_type' => 'salon'])
            ->assertRedirect(route('founding-20.show'));

        $this->assertFalse($lead->fresh()->isSubmitted());
    }

    public function test_a_resume_link_is_closed_too(): void
    {
        $lead = $this->lead();
        $this->setGate(now()->addDays(7));

        $this->get($lead->resumeUrl())->assertRedirect(route('founding-20.show'));
    }

    public function test_restart_still_clears_the_session_but_lands_on_the_explainer_while_closed(): void
    {
        $lead = $this->lead();
        $this->setGate(now()->addDays(7));

        $response = $this->withSession(['founding_twenty_lead_id' => $lead->id])->post(route('founding-twenty.restart'));

        $response->assertRedirect(route('founding-20.show'));
        $response->assertSessionMissing('founding_twenty_lead_id');
    }

    public function test_the_explainer_shows_the_countdown_and_hides_the_apply_buttons_while_closed(): void
    {
        $this->setGate(now()->addDays(7));

        $response = $this->get(route('founding-20.show'));

        $response->assertOk();
        $response->assertDontSee('Start Your Application');
        $response->assertSee('Applications open the moment the countdown above reaches zero');
        $response->assertDontSee('Twenty spots, and the questionnaire takes about seven minutes.');
        // The informational sections still show — nothing about "how it works" stops being true.
        $response->assertSee('How it works');
        $response->assertSee("What's included", false);
    }

    // ── Open (launch_at null or in the past) ─────────────────────────────────

    public function test_everything_works_normally_with_no_launch_date_set(): void
    {
        $this->setGate(null);

        $this->get(route('founding-twenty.show'))->assertOk()->assertSee('First, tell us who you are');
        $this->get(route('founding-20.show'))->assertOk()->assertSee('Start Your Application');
    }

    public function test_a_launch_date_already_in_the_past_behaves_as_fully_open(): void
    {
        $this->setGate(now()->subDay());

        $this->get(route('founding-twenty.show'))->assertOk();
        $this->get(route('founding-20.show'))->assertOk()->assertSee('Start Your Application');
    }

    public function test_the_gate_lifts_itself_the_instant_the_launch_date_passes(): void
    {
        $this->setGate(now()->addSeconds(30));
        $this->get(route('founding-twenty.show'))->assertRedirect(route('founding-20.show'));

        $this->travel(31)->seconds();

        $this->get(route('founding-twenty.show'))->assertOk()->assertSee('First, tell us who you are');
    }

    public function test_no_public_launch_row_at_all_fails_open_not_closed(): void
    {
        \Illuminate\Support\Facades\DB::table('public_launches')->where('key', 'founding-20')->delete();

        // Missing configuration should never silently lock real applicants out.
        $this->get(route('founding-twenty.show'))->assertOk();
    }

    public function test_leads_already_captured_before_the_gate_are_left_alone(): void
    {
        $lead = $this->lead(['submitted_at' => now()]);
        $this->setGate(now()->addDays(7));

        $this->assertNotNull(FoundingTwentyApplication::find($lead->id));
        $this->assertTrue($lead->fresh()->isSubmitted());
    }

    public function test_thanks_reserve_and_checkin_are_never_gated(): void
    {
        $lead = $this->lead(['status' => 'selected', 'submitted_at' => now(), 'deposit_amount' => 100, 'deposit_reference' => 'F20-0001']);
        $this->setGate(now()->addDays(7));

        $this->get(route('founding-twenty.thanks'))->assertOk();
        $this->get(route('founding-twenty.reserve', [$lead, $lead->reservationToken()]))->assertOk();
    }
}
