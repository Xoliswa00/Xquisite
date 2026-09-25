<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\FoundingTwentyApplicantMessage;
use App\Services\FoundingTwentyMessages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The parts of the programme that are about people rather than forms: nobody left in
 * silence, drop-offs visible, chasing prompted, direct approaches possible.
 */
class FoundingTwentyProgrammeGapsTest extends TestCase
{
    use RefreshDatabase;

    private function application(array $overrides = []): FoundingTwentyApplication
    {
        static $n = 0;
        $n++;

        return FoundingTwentyApplication::create(array_merge([
            'owner_name' => 'Thandi Mokoena',
            'business_name' => 'Thandi Hair Studio',
            'applicant_role' => 'owner',
            'phone' => '08212345' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
            'email' => 'thandi' . $n . '@example.com',
            'preferred_contact_method' => 'whatsapp',
            'business_type' => 'salon',
            'submitted_at' => now(),
        ], $overrides));
    }

    private function admin(): User
    {
        Permission::findOrCreate('manage-tenants', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('manage-tenants');

        return $user;
    }

    private function questionnairePayload(): array
    {
        return [
            'business_type' => 'salon',
            'pain_forgotten_appointments' => 3, 'pain_late_cancellations' => 3, 'pain_no_shows' => 3,
            'pain_double_bookings' => 3, 'pain_booking_enquiry_time' => 3, 'pain_staff_availability' => 3,
            'pain_tracking_balances' => 3, 'pain_revenue_visibility' => 3, 'pain_customer_data_organisation' => 3,
        ];
    }

    // ── Acknowledging applications ───────────────────────────────────────────

    public function test_finishing_the_questionnaire_emails_an_acknowledgement_when_we_have_an_email(): void
    {
        Notification::fake();
        $lead = $this->application(['submitted_at' => null, 'email' => 'thandi@example.com']);

        $this->withSession(['founding_twenty_lead_id' => $lead->id])->post(route('founding-twenty.submit'), $this->questionnairePayload());

        Notification::assertSentOnDemand(FoundingTwentyApplicantMessage::class, function ($n, $channels, $notifiable) {
            return $notifiable->routes['mail'] === 'thandi@example.com'
                && $n->subjectLine === 'We received your Founding 20 application'
                && str_contains($n->body, 'within 7 days, whether or not you are selected');
        });
        $this->assertNotNull($lead->fresh()->received_notified_at);
    }

    public function test_without_an_email_nothing_is_sent_and_they_appear_in_the_queue_to_acknowledge(): void
    {
        Notification::fake();
        $lead = $this->application(['submitted_at' => null, 'email' => null]);

        $this->withSession(['founding_twenty_lead_id' => $lead->id])->post(route('founding-twenty.submit'), $this->questionnairePayload());

        Notification::assertNothingSent();
        $this->assertNull($lead->fresh()->received_notified_at);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.action-queue'));
        $response->assertSee('Applications not yet acknowledged (1)');
        $response->assertSee('Thandi Hair Studio');
    }

    public function test_thank_you_page_promises_a_reply_to_everyone_not_just_the_selected(): void
    {
        $this->get(route('founding-twenty.thanks'))
            ->assertSee('within 7 days, whether or not you are selected');
    }

    // ── The decision, in words ───────────────────────────────────────────────

    public function test_each_decision_has_its_own_message_and_selected_carries_the_deposit_link(): void
    {
        $selected = $this->application(['status' => 'selected']);
        $waitlisted = $this->application(['status' => 'waitlisted']);
        $rejected = $this->application(['status' => 'rejected']);

        $yes = FoundingTwentyMessages::decision($selected);
        $this->assertStringContainsString('has been selected', $yes['body']);
        $this->assertStringContainsString(route('founding-twenty.reserve', [$selected, $selected->reservationToken()]), $yes['body']);

        $this->assertStringContainsString('waiting list', FoundingTwentyMessages::decision($waitlisted)['body']);

        $no = FoundingTwentyMessages::decision($rejected)['body'];
        $this->assertStringContainsString('not one of them this time', $no);
        $this->assertStringContainsString('keep me posted', $no);
    }

    public function test_no_message_copy_uses_an_em_dash(): void
    {
        $a = $this->application(['status' => 'selected', 'tenant_linked_at' => now()]);

        foreach (FoundingTwentyMessages::TYPES as $type) {
            $m = FoundingTwentyMessages::for($type, $a);
            $this->assertStringNotContainsString('—', $m['subject'] . $m['body'], $type);
        }
    }

    public function test_conversion_message_states_the_price_and_that_the_deposit_comes_back(): void
    {
        $body = FoundingTwentyMessages::conversion($this->application())['body'];

        $this->assertStringContainsString('R200 a month', $body);
        $this->assertStringContainsString('R100 deposit is refunded either way', $body);
    }

    // ── Sending and recording ────────────────────────────────────────────────

    public function test_emailing_a_decision_sends_it_and_records_that_they_were_told(): void
    {
        Notification::fake();
        $a = $this->application(['status' => 'waitlisted', 'email' => 'w@example.com']);

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.message.email', [$a, 'decision']))
            ->assertSessionHas('success');

        Notification::assertSentOnDemand(FoundingTwentyApplicantMessage::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === 'w@example.com');
        $this->assertNotNull($a->fresh()->decision_notified_at);
    }

    public function test_emailing_someone_with_no_email_is_refused_with_a_clear_reason(): void
    {
        Notification::fake();
        $a = $this->application(['status' => 'rejected', 'email' => null]);

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.message.email', [$a, 'decision']))->assertStatus(422);

        Notification::assertNothingSent();
    }

    public function test_a_decision_message_cannot_go_out_before_a_decision_is_made(): void
    {
        $a = $this->application(['status' => 'pending']);

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.message.told', [$a, 'decision']))->assertStatus(422);
        $this->assertNull($a->fresh()->decision_notified_at);
    }

    public function test_marking_a_whatsapp_as_sent_records_it(): void
    {
        $a = $this->application(['status' => 'rejected']);

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.message.told', [$a, 'decision']));

        $this->assertNotNull($a->fresh()->decision_notified_at);
    }

    public function test_unknown_message_types_are_not_found(): void
    {
        $a = $this->application();

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.message.told', [$a, 'nonsense']))->assertNotFound();
    }

    public function test_the_application_page_offers_the_messages_and_a_whatsapp_link_with_the_words_in_it(): void
    {
        $a = $this->application(['status' => 'rejected']);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.show', $a));

        $response->assertOk();
        $response->assertSee('Messages to them');
        $response->assertSee('Application received');
        $response->assertSee('Your decision (rejected)');
        $response->assertSee(rawurlencode('not one of them this time'), false);
    }

    // ── The action queue chases people ───────────────────────────────────────

    public function test_queue_lists_decided_applicants_who_have_not_been_told(): void
    {
        $this->application(['business_name' => 'Silent Salon', 'status' => 'rejected', 'reviewed_at' => now()->subDay()]);
        $this->application(['business_name' => 'Told Salon', 'status' => 'rejected', 'decision_notified_at' => now()]);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.action-queue'));

        $response->assertSee('Decided, but not yet told (1)');
        $response->assertSee('Silent Salon');
        $response->assertDontSee('Told Salon');
    }

    public function test_queue_flags_applications_past_the_promised_decision_time(): void
    {
        $this->application(['business_name' => 'Waited Long', 'status' => 'pending', 'submitted_at' => now()->subDays(9)]);
        $this->application(['business_name' => 'Fresh One', 'status' => 'pending', 'submitted_at' => now()->subDays(2), 'received_notified_at' => now()]);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.action-queue'));

        $response->assertSee('Past our promised decision time (1)');
        $this->assertTrue($response->viewData('overduePromise')->contains('business_name', 'Waited Long'));
        $this->assertFalse($response->viewData('overduePromise')->contains('business_name', 'Fresh One'));
    }

    public function test_queue_chases_selected_businesses_that_never_paid_the_deposit(): void
    {
        $this->application(['business_name' => 'Ghosted', 'status' => 'selected', 'decision_notified_at' => now()->subDays(6)]);
        $this->application(['business_name' => 'Paid', 'status' => 'selected', 'decision_notified_at' => now()->subDays(6), 'deposit_submitted_at' => now()]);

        $queue = $this->actingAs($this->admin())->get(route('admin.founding-twenty.action-queue'))->viewData('reservationChase');

        $this->assertSame(['Ghosted'], $queue->pluck('business_name')->all());
    }

    public function test_queue_catches_onboarded_businesses_with_no_first_win(): void
    {
        $tenant = Tenant::create(['name' => 'T', 'slug' => 't-' . uniqid(), 'email' => 't@example.com', 'is_active' => true]);
        $this->application(['business_name' => 'Stuck', 'status' => 'selected', 'tenant_id' => $tenant->id, 'tenant_linked_at' => now()->subDays(15)]);
        $this->application(['business_name' => 'Winning', 'status' => 'selected', 'tenant_linked_at' => now()->subDays(15), 'first_value_milestone_at' => now()]);
        $this->application(['business_name' => 'Too New', 'status' => 'selected', 'tenant_linked_at' => now()->subDays(3)]);

        $queue = $this->actingAs($this->admin())->get(route('admin.founding-twenty.action-queue'))->viewData('stuckOnboarding');

        $this->assertSame(['Stuck'], $queue->pluck('business_name')->all());
    }

    public function test_queue_prompts_the_conversion_conversation_two_weeks_before_the_free_period_ends(): void
    {
        $this->application(['business_name' => 'Almost Done', 'status' => 'selected', 'tenant_linked_at' => now()->subDays(77)]);
        $this->application(['business_name' => 'Already Told', 'status' => 'selected', 'tenant_linked_at' => now()->subDays(80), 'conversion_offer_sent_at' => now()]);
        $this->application(['business_name' => 'Already Paying', 'status' => 'converted', 'tenant_linked_at' => now()->subDays(80)]);

        $queue = $this->actingAs($this->admin())->get(route('admin.founding-twenty.action-queue'))->viewData('conversionDue');

        $this->assertSame(['Almost Done'], $queue->pluck('business_name')->all());
    }

    // ── Seeing where it leaks ────────────────────────────────────────────────

    public function test_the_questionnaire_reports_the_furthest_section_reached_and_never_goes_backwards(): void
    {
        $lead = $this->application(['submitted_at' => null]);
        $session = ['founding_twenty_lead_id' => $lead->id];

        $this->withSession($session)->post(route('founding-twenty.progress'), ['section' => 4])->assertNoContent();
        $this->withSession($session)->post(route('founding-twenty.progress'), ['section' => 2])->assertNoContent();
        $this->assertSame(4, $lead->fresh()->last_section_reached);

        $this->withSession($session)->post(route('founding-twenty.progress'), ['section' => 99])->assertNoContent();
        $this->assertSame(4, $lead->fresh()->last_section_reached);
    }

    public function test_progress_does_nothing_without_a_lead_in_the_session(): void
    {
        $this->post(route('founding-twenty.progress'), ['section' => 3])->assertNoContent();
    }

    public function test_the_funnel_counts_each_stage_and_where_unfinished_applicants_stopped(): void
    {
        $this->application(['submitted_at' => null, 'last_section_reached' => null, 'source' => 'tiktok']);
        $this->application(['submitted_at' => null, 'last_section_reached' => 3, 'source' => 'tiktok']);
        $this->application(['status' => 'rejected', 'source' => 'tiktok', 'reviewed_at' => now()]);
        $this->application(['status' => 'selected', 'source' => 'whatsapp', 'deposit_confirmed_at' => now(), 'tenant_linked_at' => now(), 'first_value_milestone_at' => now()]);
        $this->application(['status' => 'converted', 'source' => 'whatsapp', 'deposit_confirmed_at' => now(), 'tenant_linked_at' => now(), 'first_value_milestone_at' => now()]);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.funnel'));

        $response->assertOk();
        $this->assertSame(
            [5, 3, 2, 2, 2, 2, 1],
            array_values($response->viewData('stages'))
        );
        $this->assertSame([0 => 1, 3 => 1], $response->viewData('dropOff')->all());
        $this->assertSame(['started' => 3, 'finished' => 1, 'selected' => 0, 'converted' => 0], $response->viewData('bySource')['tiktok']);
        $this->assertSame(['started' => 2, 'finished' => 2, 'selected' => 2, 'converted' => 1], $response->viewData('bySource')['whatsapp']);
        $response->assertSee('Stopped in section 3 of 8');
        $response->assertSee('Never got to the questions');
    }

    public function test_the_funnel_is_empty_safe(): void
    {
        $this->actingAs($this->admin())->get(route('admin.founding-twenty.funnel'))
            ->assertOk()
            ->assertSee('Nobody has dropped out yet.')
            ->assertSee('No decisions yet');
    }

    // ── Approaching businesses directly ──────────────────────────────────────

    private function direct(array $overrides = []): array
    {
        return array_merge([
            'business_name' => 'Struggling Salon',
            'owner_name' => 'Nomsa Dlamini',
            'phone' => '0731234567',
            'business_type' => 'salon',
            'admin_notes' => 'Runs everything from a paper diary.',
            'consent_confirmed' => '1',
        ], $overrides);
    }

    public function test_a_business_approached_directly_joins_the_review_list_without_the_questionnaire(): void
    {
        $response = $this->actingAs($this->admin())->post(route('admin.founding-twenty.store-direct'), $this->direct());

        $a = FoundingTwentyApplication::firstOrFail();
        $response->assertRedirect(route('admin.founding-twenty.show', $a));
        $this->assertSame('direct', $a->source);
        $this->assertTrue($a->isSubmitted());
        $this->assertNull($a->score);
        $this->assertNotNull($a->privacy_consented_at);
        $this->assertSame('Runs everything from a paper diary.', $a->admin_notes);
        $this->actingAs($this->admin())->get(route('admin.founding-twenty.index'))->assertSee('Struggling Salon');
    }

    public function test_adding_a_business_needs_confirmation_that_they_agreed_to_being_kept_on_file(): void
    {
        $this->actingAs($this->admin())->post(route('admin.founding-twenty.store-direct'), $this->direct(['consent_confirmed' => '']))
            ->assertSessionHasErrors('consent_confirmed');

        $this->assertSame(0, FoundingTwentyApplication::count());
    }

    public function test_adding_a_number_already_in_the_programme_opens_the_existing_record(): void
    {
        $existing = $this->application(['phone' => '073 123 4567']);

        $response = $this->actingAs($this->admin())->post(route('admin.founding-twenty.store-direct'), $this->direct());

        $response->assertRedirect(route('admin.founding-twenty.show', $existing));
        $this->assertSame(1, FoundingTwentyApplication::count());
    }

    public function test_the_add_business_form_renders(): void
    {
        $this->actingAs($this->admin())->get(route('admin.founding-twenty.create'))->assertOk()->assertSee('Add a business');
    }

    // ── Data we keep ─────────────────────────────────────────────────────────

    public function test_the_privacy_policy_covers_programme_applications_and_how_long_they_are_kept(): void
    {
        $this->get(route('privacy'))
            ->assertSee('Programme applications')
            ->assertSee('up to 12 months');
    }

    public function test_purge_dry_run_deletes_nothing_and_force_removes_only_stale_unselected_applications(): void
    {
        $oldLead = $this->application(['submitted_at' => null]);
        $oldRejected = $this->application(['status' => 'rejected']);
        $oldSelected = $this->application(['status' => 'selected']);
        $recentRejected = $this->application(['status' => 'rejected']);
        foreach ([$oldLead, $oldRejected, $oldSelected] as $a) {
            $a->forceFill(['created_at' => now()->subMonths(14)])->saveQuietly();
        }

        $this->artisan('founding-twenty:purge-stale')->assertSuccessful();
        $this->assertSame(4, FoundingTwentyApplication::count());

        $this->artisan('founding-twenty:purge-stale', ['--force' => true])->assertSuccessful();
        $this->assertNull(FoundingTwentyApplication::find($oldLead->id));
        $this->assertNull(FoundingTwentyApplication::find($oldRejected->id));
        $this->assertNotNull(FoundingTwentyApplication::find($oldSelected->id));
        $this->assertNotNull(FoundingTwentyApplication::find($recentRejected->id));
    }
}
