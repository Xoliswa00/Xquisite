<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\OutreachCampaign;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Two steps: (1) about you, which saves them as a lead straight away, then
 * (2) the questionnaire, which completes that same application. Someone who
 * gives up halfway is still someone we know and can follow up with.
 */
class FoundingTwentyLeadFlowTest extends TestCase
{
    use RefreshDatabase;

    private const SESSION_KEY = 'founding_twenty_lead_id';

    private function stepOne(array $overrides = []): array
    {
        return array_merge([
            'owner_name' => 'Thandi Mokoena',
            'business_name' => 'Thandi Hair Studio',
            'applicant_role' => 'owner',
            'phone' => '0821234567',
            'preferred_contact_method' => 'whatsapp',
            'why_founding_20' => 'I lose bookings to WhatsApp chaos.',
            'heard_about_via' => 'tiktok',
            'privacy_consent' => '1',
        ], $overrides);
    }

    private function stepTwo(array $overrides = []): array
    {
        return array_merge([
            'business_type' => 'salon',
            'pain_forgotten_appointments' => 3, 'pain_late_cancellations' => 3, 'pain_no_shows' => 3,
            'pain_double_bookings' => 3, 'pain_booking_enquiry_time' => 3, 'pain_staff_availability' => 3,
            'pain_tracking_balances' => 3, 'pain_revenue_visibility' => 3, 'pain_customer_data_organisation' => 3,
        ], $overrides);
    }

    private function lead(array $overrides = []): FoundingTwentyApplication
    {
        return FoundingTwentyApplication::create(array_merge([
            'owner_name' => 'Thandi Mokoena',
            'business_name' => 'Thandi Hair Studio',
            'applicant_role' => 'owner',
            'phone' => '0821234567',
            'preferred_contact_method' => 'whatsapp',
            'privacy_consented_at' => now(),
        ], $overrides));
    }

    private function admin(): User
    {
        Permission::findOrCreate('manage-tenants', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('manage-tenants');

        return $user;
    }

    // ── Step 1: who are we talking to ────────────────────────────────────────

    public function test_step_one_asks_who_they_are_before_any_business_questions(): void
    {
        $response = $this->get(route('founding-twenty.show'));

        $response->assertOk();
        $response->assertSee('First, tell us who you are');
        $response->assertSee('Step 1 of 2');
        $response->assertSee('type="tel"', false);
        $response->assertSee('Why would you like to be part of Founding 20?');
        $response->assertSee('How did you hear about this?');
        $response->assertDontSee('Section 1 of 8');
        $response->assertDontSee('pain_no_shows');
    }

    public function test_step_one_saves_them_as_a_lead_before_any_questionnaire(): void
    {
        $response = $this->post(route('founding-twenty.store'), $this->stepOne());

        $response->assertRedirect(route('founding-twenty.questions'));
        $lead = FoundingTwentyApplication::firstOrFail();
        $this->assertSame('Thandi Mokoena', $lead->owner_name);
        $this->assertSame('owner', $lead->applicant_role);
        $this->assertSame('I lose bookings to WhatsApp chaos.', $lead->why_founding_20);
        $this->assertSame('tiktok', $lead->heard_about_via);
        $this->assertNotNull($lead->privacy_consented_at);
        $this->assertNull($lead->submitted_at);
        $this->assertNull($lead->business_type);
        $this->assertNull($lead->score);
        $this->assertFalse($lead->isSubmitted());
        $response->assertSessionHas(self::SESSION_KEY, $lead->id);
    }

    public function test_step_one_only_asks_for_what_it_needs_in_plain_language(): void
    {
        $response = $this->from(route('founding-twenty.show'))->post(route('founding-twenty.store'), [
            'preferred_contact_method' => 'whatsapp',
        ]);

        $response->assertRedirect(route('founding-twenty.show'));
        $errors = session('errors');
        $this->assertSame('Please tell us your name.', $errors->first('owner_name'));
        $this->assertSame('Please tell us your business name.', $errors->first('business_name'));
        $this->assertSame('Please tell us your role in the business.', $errors->first('applicant_role'));
        $this->assertSame('Please add your phone number so we can reach you.', $errors->first('phone'));
        $this->assertStringContainsString('happy for us to save your details', $errors->first('privacy_consent'));
        $this->assertSame(0, FoundingTwentyApplication::count());
    }

    public function test_the_why_and_where_from_answers_are_optional(): void
    {
        $this->post(route('founding-twenty.store'), $this->stepOne(['why_founding_20' => '', 'heard_about_via' => '']))
            ->assertSessionDoesntHaveErrors();
    }

    public function test_email_is_required_when_email_is_the_preferred_contact_method(): void
    {
        $response = $this->from(route('founding-twenty.show'))->post(
            route('founding-twenty.store'),
            $this->stepOne(['preferred_contact_method' => 'email'])
        );

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('since you chose email', session('errors')->first('email'));
    }

    public function test_email_stays_optional_for_whatsapp_and_call(): void
    {
        foreach (['whatsapp' => '0821234567', 'call' => '0831234567'] as $method => $phone) {
            $this->post(route('founding-twenty.store'), $this->stepOne(['preferred_contact_method' => $method, 'phone' => $phone]))
                ->assertSessionDoesntHaveErrors();
        }
    }

    public function test_where_they_arrived_from_is_kept_on_the_lead(): void
    {
        $campaign = OutreachCampaign::create(['name' => 'September TikTok wave']);

        $this->post(route('founding-twenty.store'), $this->stepOne(['source' => 'tiktok-bio', 'outreach_campaign_id' => $campaign->id]));

        $lead = FoundingTwentyApplication::firstOrFail();
        $this->assertSame('tiktok-bio', $lead->source);
        $this->assertSame($campaign->id, $lead->outreach_campaign_id);
    }

    public function test_the_same_number_coming_back_is_one_lead_not_two(): void
    {
        $this->post(route('founding-twenty.store'), $this->stepOne(['phone' => '082 123 4567', 'why_founding_20' => 'First try']));
        $this->post(route('founding-twenty.restart'));
        $this->post(route('founding-twenty.store'), $this->stepOne(['phone' => '0821234567', 'why_founding_20' => 'Second try']));

        $this->assertSame(1, FoundingTwentyApplication::count());
        $this->assertSame('Second try', FoundingTwentyApplication::first()->why_founding_20);
    }

    public function test_someone_who_already_applied_is_told_so_instead_of_starting_again(): void
    {
        $this->lead(['submitted_at' => now(), 'business_type' => 'salon']);

        $response = $this->from(route('founding-twenty.show'))->post(route('founding-twenty.store'), $this->stepOne());

        $response->assertSessionHasErrors('phone');
        $this->assertStringContainsString('already have an application from this number', session('errors')->first('phone'));
        $this->assertSame(1, FoundingTwentyApplication::count());
    }

    public function test_someone_partway_through_who_returns_to_the_start_is_sent_back_to_where_they_were(): void
    {
        $lead = $this->lead();

        $this->withSession([self::SESSION_KEY => $lead->id])
            ->get(route('founding-twenty.show'))
            ->assertRedirect(route('founding-twenty.questions'));
    }

    // ── Step 2: the questionnaire, for that lead ─────────────────────────────

    public function test_the_questionnaire_needs_step_one_first(): void
    {
        $this->get(route('founding-twenty.questions'))->assertRedirect(route('founding-twenty.show'));
    }

    public function test_the_questionnaire_speaks_to_them_by_name_and_gives_a_resume_link(): void
    {
        $lead = $this->lead();

        $response = $this->withSession([self::SESSION_KEY => $lead->id])->get(route('founding-twenty.questions'));

        $response->assertOk();
        $response->assertSee('Thanks, Thandi. Now tell us about Thandi Hair Studio.');
        $response->assertSee('Section 1 of 8');
        $response->assertSee($lead->resumeUrl(), false);
        $response->assertSee('Not Thandi? Start again');
        // Already known from step 1, so not asked twice.
        $response->assertDontSee('name="phone"', false);
        $response->assertDontSee('name="owner_name"', false);
        $response->assertDontSee('name="privacy_consent"', false);
    }

    public function test_completing_the_questionnaire_finishes_the_same_application(): void
    {
        $lead = $this->lead();

        $response = $this->withSession([self::SESSION_KEY => $lead->id])
            ->post(route('founding-twenty.submit'), $this->stepTwo());

        $response->assertRedirect(route('founding-twenty.thanks'));
        $response->assertSessionHas('applicant_first_name', 'Thandi');
        $response->assertSessionMissing(self::SESSION_KEY);
        $this->assertSame(1, FoundingTwentyApplication::count());
        $lead->refresh();
        $this->assertTrue($lead->isSubmitted());
        $this->assertSame('salon', $lead->business_type);
        $this->assertNotNull($lead->score);
        $this->assertNull($lead->value_rating);
        $this->assertNull($lead->continuation_likelihood);
    }

    public function test_the_thank_you_page_uses_their_first_name(): void
    {
        $this->withSession(['applicant_first_name' => 'Thandi'])
            ->get(route('founding-twenty.thanks'))
            ->assertSee('Thank you, Thandi, your application is in');
    }

    public function test_a_missing_rating_names_the_question_in_plain_language(): void
    {
        $lead = $this->lead();
        $payload = $this->stepTwo();
        unset($payload['pain_no_shows']);

        $response = $this->withSession([self::SESSION_KEY => $lead->id])
            ->from(route('founding-twenty.questions'))
            ->post(route('founding-twenty.submit'), $payload);

        $response->assertRedirect(route('founding-twenty.questions'));
        $this->assertSame(
            'Please rate "Clients not showing up at all" from 1 to 5 (Section 3).',
            session('errors')->first('pain_no_shows')
        );
        $this->assertFalse($lead->fresh()->isSubmitted());
    }

    public function test_ticked_checkboxes_survive_a_validation_error(): void
    {
        $lead = $this->lead();
        $payload = $this->stepTwo([
            'booking_methods' => ['whatsapp', 'website'],
            'priority_features' => ['payments'],
        ]);
        unset($payload['pain_no_shows']);

        $html = $this->withSession([self::SESSION_KEY => $lead->id])
            ->followingRedirects()
            ->from(route('founding-twenty.questions'))
            ->post(route('founding-twenty.submit'), $payload)
            ->getContent();

        $this->assertMatchesRegularExpression('/name="booking_methods\[\]" value="whatsapp"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/name="booking_methods\[\]" value="website"\s+checked/', $html);
        $this->assertMatchesRegularExpression('/name="priority_features\[\]" value="payments"\s+checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/name="booking_methods\[\]" value="phone"\s+checked/', $html);
    }

    public function test_submitting_without_a_session_sends_them_back_to_step_one(): void
    {
        $response = $this->post(route('founding-twenty.submit'), $this->stepTwo());

        $response->assertRedirect(route('founding-twenty.show'));
        $response->assertSessionHasErrors('session');
    }

    // ── Picking it back up ───────────────────────────────────────────────────

    public function test_the_resume_link_puts_them_back_on_the_questionnaire_on_any_device(): void
    {
        $lead = $this->lead();

        $response = $this->get($lead->resumeUrl());

        $response->assertRedirect(route('founding-twenty.questions'));
        $response->assertSessionHas(self::SESSION_KEY, $lead->id);
    }

    public function test_a_tampered_resume_link_is_refused(): void
    {
        $lead = $this->lead();

        $this->get(route('founding-twenty.resume', [$lead, str_repeat('a', 64)]))->assertForbidden();
    }

    public function test_the_resume_token_cannot_be_used_as_a_deposit_link_token(): void
    {
        $lead = $this->lead();

        $this->assertNotSame($lead->reservationToken(), $lead->resumeToken());
    }

    public function test_resuming_an_application_already_submitted_just_says_thanks(): void
    {
        $lead = $this->lead(['submitted_at' => now(), 'business_type' => 'salon']);

        $this->get($lead->resumeUrl())->assertRedirect(route('founding-twenty.thanks'));
    }

    public function test_not_you_clears_the_session_and_starts_again(): void
    {
        $lead = $this->lead();

        $response = $this->withSession([self::SESSION_KEY => $lead->id])->post(route('founding-twenty.restart'));

        $response->assertRedirect(route('founding-twenty.show'));
        $response->assertSessionMissing(self::SESSION_KEY);
    }

    // ── What you see as the admin ────────────────────────────────────────────

    public function test_unfinished_leads_are_listed_separately_with_a_nudge_that_carries_their_link(): void
    {
        $lead = $this->lead();
        $this->lead(['phone' => '0831234567', 'owner_name' => 'Finished Person', 'business_name' => 'Done Salon', 'submitted_at' => now(), 'business_type' => 'salon']);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.index'));

        $response->assertOk();
        $response->assertSee('Started, not finished (1)');
        $response->assertSee('Thandi Hair Studio');
        $response->assertSee('https://wa.me/27821234567', false);
        $response->assertSee(rawurlencode($lead->resumeToken()), false);
        // The finished one is in the ranked list, and the stats only count finished applications.
        $response->assertSee('Done Salon');
        $this->assertSame(1, $response->viewData('stats')['total']);
    }

    public function test_the_admin_detail_page_shows_the_person_and_flags_an_unfinished_lead(): void
    {
        $lead = $this->lead(['why_founding_20' => 'I lose bookings to WhatsApp chaos.', 'heard_about_via' => 'instagram_facebook']);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.show', $lead));

        $response->assertOk();
        $response->assertSee('The person');
        $response->assertSee('Owns the business');
        $response->assertSee('I lose bookings to WhatsApp chaos.');
        $response->assertSee('instagram facebook');
        $response->assertSee("hasn't finished the questionnaire", false);
    }

    public function test_leads_do_not_pollute_a_campaigns_industry_breakdown(): void
    {
        $campaign = OutreachCampaign::create(['name' => 'Wave 1']);
        $this->lead(['outreach_campaign_id' => $campaign->id]);
        $this->lead(['phone' => '0831234567', 'outreach_campaign_id' => $campaign->id, 'business_type' => 'salon', 'submitted_at' => now()]);

        $breakdown = $campaign->fresh()->industryBreakdown();

        $this->assertSame(['salon'], $breakdown->keys()->all());
        $this->assertSame(1, $breakdown['salon']['applied']);
    }
}
