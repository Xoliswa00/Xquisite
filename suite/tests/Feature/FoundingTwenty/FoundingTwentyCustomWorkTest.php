<?php

namespace Tests\Feature\FoundingTwenty;

use App\Models\FoundingTwentyApplication;
use App\Models\PublicLaunch;
use App\Models\User;
use App\Notifications\FoundingTwentyApplicantMessage;
use App\Services\FoundingTwentyMessages;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * A second, shorter intake into the same programme for businesses that don't run
 * on bookings — one step, no scored questionnaire, same offer, same admin pipeline.
 */
class FoundingTwentyCustomWorkTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'owner_name' => 'Nomsa Dlamini',
            'business_name' => 'Dlamini Logistics',
            'business_type_other' => 'A small logistics and delivery company',
            'custom_solution_description' => 'We need a system to track deliveries and driver availability in real time.',
            'phone' => '0731234567',
            'preferred_contact_method' => 'whatsapp',
            'privacy_consent' => '1',
        ], $overrides);
    }

    private function admin(): User
    {
        Permission::findOrCreate('manage-tenants', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('manage-tenants');

        return $user;
    }

    // ── Submitting ────────────────────────────────────────────────────────────

    public function test_submitting_creates_a_completed_application_on_the_custom_track_with_no_score(): void
    {
        $response = $this->post(route('founding-twenty.custom-work.store'), $this->payload());

        $response->assertRedirect(route('founding-twenty.thanks'));
        $a = FoundingTwentyApplication::firstOrFail();
        $this->assertSame('custom', $a->track);
        $this->assertTrue($a->isCustomTrack());
        $this->assertTrue($a->isSubmitted());
        $this->assertSame('We need a system to track deliveries and driver availability in real time.', $a->custom_solution_description);
        $this->assertSame('other', $a->business_type);
        $this->assertSame('A small logistics and delivery company', $a->business_type_other);
        $this->assertNull($a->score);
        $this->assertNull($a->tier);
        $this->assertNull($a->custom_monthly_price);
    }

    public function test_no_second_step_required_it_is_submitted_immediately(): void
    {
        $this->post(route('founding-twenty.custom-work.store'), $this->payload());

        $this->assertNotNull(FoundingTwentyApplication::first()->submitted_at);
    }

    public function test_an_acknowledgement_email_goes_out_when_they_gave_one(): void
    {
        Notification::fake();

        $this->post(route('founding-twenty.custom-work.store'), $this->payload(['preferred_contact_method' => 'email', 'email' => 'nomsa@example.com']));

        Notification::assertSentOnDemand(FoundingTwentyApplicantMessage::class, fn ($n, $c, $notifiable) => $notifiable->routes['mail'] === 'nomsa@example.com');
        $this->assertNotNull(FoundingTwentyApplication::first()->received_notified_at);
    }

    public function test_what_they_need_and_what_their_business_does_are_both_required(): void
    {
        $response = $this->from(route('founding-20.show'))->post(route('founding-twenty.custom-work.store'), $this->payload(['custom_solution_description' => '', 'business_type_other' => '']));

        $response->assertSessionHasErrors(['custom_solution_description', 'business_type_other']);
        $this->assertSame(0, FoundingTwentyApplication::count());
    }

    public function test_the_description_needs_more_than_a_couple_of_words(): void
    {
        $this->post(route('founding-twenty.custom-work.store'), $this->payload(['custom_solution_description' => 'A website']))
            ->assertSessionHasErrors('custom_solution_description');
    }

    public function test_email_is_required_only_when_chosen_as_the_contact_method(): void
    {
        $this->post(route('founding-twenty.custom-work.store'), $this->payload(['preferred_contact_method' => 'email', 'email' => '']))
            ->assertSessionHasErrors('email');

        $this->post(route('founding-twenty.custom-work.store'), $this->payload(['preferred_contact_method' => 'whatsapp', 'email' => '']))
            ->assertSessionDoesntHaveErrors();
    }

    // ── Shared with the booking track ────────────────────────────────────────

    public function test_the_same_phone_number_twice_is_refused_the_second_time_not_duplicated(): void
    {
        // Unlike the booking track's step-1 lead, a custom-work submission is complete and
        // submitted the moment it's sent — there's no "unfinished" state to update into, so a
        // second submission from the same number is refused exactly like re-submitting a
        // finished booking application, not silently merged over the first one.
        $this->post(route('founding-twenty.custom-work.store'), $this->payload(['phone' => '073 123 4567']))
            ->assertRedirect(route('founding-twenty.thanks'));

        $response = $this->post(route('founding-twenty.custom-work.store'), $this->payload(['phone' => '0731234567', 'custom_solution_description' => 'A second, different request.']));

        $response->assertSessionHasErrors('phone');
        $this->assertSame(1, FoundingTwentyApplication::count());
        $this->assertSame('We need a system to track deliveries and driver availability in real time.', FoundingTwentyApplication::first()->custom_solution_description);
    }

    public function test_a_number_that_already_has_a_submitted_application_is_told_so(): void
    {
        FoundingTwentyApplication::create([
            'owner_name' => 'Someone', 'business_name' => 'Existing Biz', 'phone' => '0731234567',
            'preferred_contact_method' => 'whatsapp', 'submitted_at' => now(),
        ]);

        $response = $this->post(route('founding-twenty.custom-work.store'), $this->payload());

        $response->assertSessionHasErrors('phone');
        $this->assertSame(1, FoundingTwentyApplication::count());
    }

    public function test_it_is_closed_by_the_same_launch_gate_as_the_booking_track(): void
    {
        PublicLaunch::updateOrCreate(['key' => 'founding-20'], ['title' => 'Founding 20', 'launch_at' => now()->addDays(7)]);

        $response = $this->post(route('founding-twenty.custom-work.store'), $this->payload());

        $response->assertRedirect(route('founding-20.show'));
        $this->assertSame(0, FoundingTwentyApplication::count());
    }

    public function test_the_section_is_hidden_from_the_explainer_while_the_gate_is_closed(): void
    {
        PublicLaunch::updateOrCreate(['key' => 'founding-20'], ['title' => 'Founding 20', 'launch_at' => now()->addDays(7), 'benefits' => ['x']]);

        $this->get(route('founding-20.show'))->assertDontSee('Not a booking-based business?');
    }

    public function test_the_section_shows_on_the_explainer_when_open(): void
    {
        $this->get(route('founding-20.show'))
            ->assertSee('Not a booking-based business?')
            ->assertSee('Tell us what you need');
    }

    // ── Pricing and messaging ─────────────────────────────────────────────────

    public function test_monthly_price_falls_back_to_the_standard_rate_until_a_module_price_is_set(): void
    {
        $a = FoundingTwentyApplication::create([
            'owner_name' => 'N', 'business_name' => 'B', 'phone' => '0731234567',
            'preferred_contact_method' => 'whatsapp', 'track' => 'custom', 'submitted_at' => now(),
        ]);

        $this->assertSame((float) config('founding_twenty.monthly_price'), $a->monthlyPrice());

        $a->update(['custom_monthly_price' => 350]);
        $this->assertSame(350.0, $a->fresh()->monthlyPrice());
    }

    public function test_the_conversion_message_is_honest_when_no_price_has_been_set_yet(): void
    {
        $a = FoundingTwentyApplication::create([
            'owner_name' => 'Nomsa', 'business_name' => 'Dlamini Logistics', 'phone' => '0731234567',
            'preferred_contact_method' => 'whatsapp', 'track' => 'custom', 'submitted_at' => now(),
        ]);

        $body = FoundingTwentyMessages::conversion($a)['body'];

        $this->assertStringContainsString('we will confirm your monthly price based on it', $body);
        $this->assertStringNotContainsString('R200 a month', $body);
    }

    public function test_the_conversion_message_uses_the_agreed_module_price_once_set(): void
    {
        $a = FoundingTwentyApplication::create([
            'owner_name' => 'Nomsa', 'business_name' => 'Dlamini Logistics', 'phone' => '0731234567',
            'preferred_contact_method' => 'whatsapp', 'track' => 'custom', 'submitted_at' => now(),
            'custom_monthly_price' => 350,
        ]);

        $body = FoundingTwentyMessages::conversion($a)['body'];

        $this->assertStringContainsString('R350 a month', $body);
        $this->assertStringContainsString('locked for 24 months', $body);
    }

    public function test_booking_track_applications_are_unaffected_by_any_of_this(): void
    {
        $a = FoundingTwentyApplication::create([
            'owner_name' => 'B', 'business_name' => 'Booking Salon', 'phone' => '0821234567',
            'preferred_contact_method' => 'whatsapp', 'business_type' => 'salon', 'submitted_at' => now(),
        ]);

        $this->assertSame('booking', $a->track);
        $this->assertFalse($a->isCustomTrack());
        $this->assertStringContainsString('R200 a month', FoundingTwentyMessages::conversion($a)['body']);
    }

    // ── The admin side ───────────────────────────────────────────────────────

    public function test_the_admin_can_see_the_custom_request_and_set_a_module_price(): void
    {
        $a = FoundingTwentyApplication::create([
            'owner_name' => 'Nomsa Dlamini', 'business_name' => 'Dlamini Logistics', 'phone' => '0731234567',
            'preferred_contact_method' => 'whatsapp', 'track' => 'custom', 'submitted_at' => now(),
            'business_type' => 'other', 'business_type_other' => 'Logistics',
            'custom_solution_description' => 'Delivery tracking system.',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.show', $a));

        $response->assertOk();
        $response->assertSee('Delivery tracking system.');
        $response->assertSee('Custom');

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.custom-price', $a), ['custom_monthly_price' => 400, 'custom_pricing_notes' => 'Turned into a reusable logistics module.']);

        $a->refresh();
        $this->assertEquals(400, $a->custom_monthly_price);
        $this->assertSame('Turned into a reusable logistics module.', $a->custom_pricing_notes);
    }

    public function test_a_price_cannot_be_set_on_a_booking_track_application(): void
    {
        $a = FoundingTwentyApplication::create([
            'owner_name' => 'B', 'business_name' => 'Booking Salon', 'phone' => '0821234567',
            'preferred_contact_method' => 'whatsapp', 'business_type' => 'salon', 'submitted_at' => now(),
        ]);

        $this->actingAs($this->admin())->post(route('admin.founding-twenty.custom-price', $a), ['custom_monthly_price' => 400])
            ->assertStatus(422);
    }

    public function test_custom_track_applications_are_listed_separately_from_the_scored_ranking(): void
    {
        FoundingTwentyApplication::create([
            'owner_name' => 'Scored', 'business_name' => 'Scored Salon', 'phone' => '0821111111',
            'preferred_contact_method' => 'whatsapp', 'business_type' => 'salon', 'submitted_at' => now(), 'score' => 80, 'tier' => 'high',
        ]);
        FoundingTwentyApplication::create([
            'owner_name' => 'Custom', 'business_name' => 'Custom Biz', 'phone' => '0822222222',
            'preferred_contact_method' => 'whatsapp', 'track' => 'custom', 'submitted_at' => now(),
            'custom_solution_description' => 'Needs a stock system.',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.founding-twenty.index'));

        $response->assertSee('Custom work requests (1)');
        $response->assertSee('Custom Biz');
        // The scored table's applications collection stays booking-only, so a null score never lands at the top of it.
        $applications = $response->viewData('applications');
        $this->assertFalse($applications->contains('business_name', 'Custom Biz'));
        $this->assertSame(['Scored Salon'], $applications->pluck('business_name')->all());
    }
}
