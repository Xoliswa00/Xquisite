<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Notifications\AppNotice;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * A quote from photos can't hold a slot forever: it expires after the
 * business's window (default 48h) or 24h before the appointment, whichever
 * is first; the client gets one reminder halfway; an unanswered quote cancels
 * the booking and frees the slot.
 */
class QuoteExpiryTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Service $service;
    private Customer $customer;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seed(PermissionRoleSeeder::class);
        $this->travelTo(Carbon::parse('2026-10-12 10:00:00'));

        $this->tenant = Tenant::create(['name' => 'Kasi Braids', 'slug' => 'kasi-braids', 'email' => 'kb@example.com', 'is_active' => true]);
        $this->tenant->activateModule('booking');
        TenantContext::set($this->tenant->id);

        $this->service = Service::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Waist-length Knotless', 'duration_minutes' => 1440,
            'price' => 900, 'pricing_type' => 'flat', 'is_active' => true, 'requires_quote' => true,
        ]);
        $this->customer = Customer::create(['tenant_id' => $this->tenant->id, 'name' => 'Lindiwe', 'email' => 'lindiwe@example.com', 'is_active' => true]);

        $this->staff = User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
        $this->staff->assignRole('employee');
    }

    private function booking(Carbon $at): Appointment
    {
        $appt = Appointment::create([
            'tenant_id' => $this->tenant->id, 'customer_id' => $this->customer->id,
            'scheduled_at' => $at, 'duration_minutes' => 1440, 'status' => 'pending', 'quote_status' => 'requested',
        ]);
        $appt->services()->attach($this->service->id, ['duration_minutes' => 1440, 'price_at_booking' => 900, 'quantity' => 1, 'sort_order' => 0]);

        return $appt;
    }

    private function sendQuote(Appointment $appt, float $price = 1450): void
    {
        TenantContext::clear();
        $this->actingAs($this->staff)
            ->post(route('appointments.quote.send', $appt), ['quoted_price' => $price, 'quoted_duration_minutes' => 1500])
            ->assertSessionHas('success');
    }

    private function runJob(): void
    {
        TenantContext::clear();
        $this->artisan('booking:expire-quotes')->assertSuccessful();
    }

    // ── The deadline ────────────────────────────────────────────────────────

    public function test_a_quote_expires_48_hours_after_it_is_sent(): void
    {
        $appt = $this->booking(now()->addDays(7));

        $this->sendQuote($appt);

        $this->assertEquals(now()->addHours(48), $appt->fresh()->quote_expires_at);
    }

    public function test_or_24_hours_before_the_appointment_if_that_is_sooner(): void
    {
        $appt = $this->booking(now()->addHours(60));

        $this->sendQuote($appt);

        $this->assertEquals(now()->addHours(36), $appt->fresh()->quote_expires_at, '60h away minus the 24h cut-off');
    }

    public function test_a_late_quote_still_gives_the_client_two_hours(): void
    {
        $appt = $this->booking(now()->addHours(10));

        $this->sendQuote($appt);

        $this->assertEquals(now()->addHours(2), $appt->fresh()->quote_expires_at);
    }

    public function test_the_owner_can_change_the_window(): void
    {
        $this->tenant->update(['quote_expiry_hours' => 24]);
        $appt = $this->booking(now()->addDays(7));

        $this->sendQuote($appt);

        $this->assertEquals(now()->addHours(24), $appt->fresh()->quote_expires_at);
    }

    // ── Reminder and expiry ─────────────────────────────────────────────────

    public function test_the_client_gets_one_reminder_halfway(): void
    {
        $appt = $this->booking(now()->addDays(7));
        $this->sendQuote($appt);
        Notification::fake();

        $this->travel(23)->hours();
        $this->runJob();
        Notification::assertNothingSent();

        $this->travel(2)->hours(); // 25h in: past halfway
        $this->runJob();
        $this->runJob();

        Notification::assertSentToTimes($this->customer, AppNotice::class, 1);
        Notification::assertSentTo($this->customer, AppNotice::class,
            fn (AppNotice $n) => $n->toArray($this->customer)['title'] === 'Your quote expires soon');
        $this->assertSame('sent', $appt->fresh()->quote_status, 'still open');
    }

    public function test_an_unanswered_quote_cancels_the_booking_and_tells_both_sides(): void
    {
        $appt = $this->booking(now()->addDays(7));
        $this->sendQuote($appt);
        Notification::fake();

        $this->travel(49)->hours();
        $this->runJob();

        $appt->refresh();
        $this->assertSame('expired', $appt->quote_status);
        $this->assertSame('cancelled', $appt->status, 'the slot is free again');
        Notification::assertSentTo($this->customer, AppNotice::class,
            fn (AppNotice $n) => $n->toArray($this->customer)['title'] === 'Your quote expired');
        Notification::assertSentTo($this->staff, AppNotice::class,
            fn (AppNotice $n) => $n->toArray($this->staff)['title'] === 'Quote expired');

        // Running again does nothing more.
        Notification::fake();
        $this->runJob();
        Notification::assertNothingSent();
    }

    public function test_an_accepted_quote_never_expires(): void
    {
        $appt = $this->booking(now()->addDays(7));
        $this->sendQuote($appt);
        $this->actingAs($this->customer, 'customer')
            ->post(route('book.quote.accept', [$this->tenant->slug, $appt]))->assertSessionHas('success');

        $this->travel(72)->hours();
        $this->runJob();

        $this->assertSame('accepted', $appt->fresh()->quote_status);
        $this->assertSame('pending', $appt->fresh()->status);
    }

    public function test_a_quote_cannot_be_accepted_after_its_deadline_even_before_the_job_runs(): void
    {
        $appt = $this->booking(now()->addDays(7));
        $this->sendQuote($appt);

        $this->travel(49)->hours();
        $this->actingAs($this->customer, 'customer')
            ->post(route('book.quote.accept', [$this->tenant->slug, $appt]))
            ->assertSessionHasErrors('quote');

        $this->assertSame('sent', $appt->fresh()->quote_status);
    }

    public function test_a_revised_quote_gets_a_fresh_deadline_and_reminder(): void
    {
        $appt = $this->booking(now()->addDays(7));
        $this->sendQuote($appt);
        $this->travel(30)->hours();
        $this->runJob(); // reminder sent
        $this->assertNotNull($appt->fresh()->quote_reminded_at);

        $this->sendQuote($appt, 1300);

        $appt->refresh();
        $this->assertEquals(now()->addHours(48), $appt->quote_expires_at);
        $this->assertNull($appt->quote_reminded_at);
    }

    // ── What people see ─────────────────────────────────────────────────────

    public function test_the_client_and_staff_see_the_deadline(): void
    {
        $appt = $this->booking(now()->addDays(7));
        $this->sendQuote($appt);
        $deadline = $appt->fresh()->quote_expires_at->format('D d M, H:i');

        $this->actingAs($this->customer, 'customer')->get(route('book.my-bookings', $this->tenant->slug))
            ->assertSee("Accept by {$deadline}");

        TenantContext::clear();
        $this->actingAs($this->staff)->get(route('appointments.show', $appt))
            ->assertSee("The client has until {$deadline}");
    }

    public function test_the_setting_saves_and_other_profile_forms_leave_it_alone(): void
    {
        $owner = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $owner->assignRole('tenant-owner');
        TenantContext::clear();
        $base = ['business_name' => 'Kasi Braids', 'slug' => 'kasi-braids', 'email' => 'kb@example.com'];

        $this->actingAs($owner)->patch(route('profile.business.update'), $base + ['quote_expiry_hours' => 72])->assertSessionHasNoErrors();
        $this->assertSame(72, $this->tenant->fresh()->quote_expiry_hours);

        // The business-details and banking forms post without the field.
        $this->actingAs($owner)->patch(route('profile.business.update'), $base)->assertSessionHasNoErrors();
        $this->assertSame(72, $this->tenant->fresh()->quote_expiry_hours);

        $this->actingAs($owner)->patch(route('profile.business.update'), $base + ['quote_expiry_hours' => 1])
            ->assertSessionHasErrors('quote_expiry_hours');
    }
}
