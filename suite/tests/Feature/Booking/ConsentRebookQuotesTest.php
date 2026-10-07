<?php

namespace Tests\Feature\Booking;

use App\Mail\QuoteReadyEmail;
use App\Mail\RebookReminderEmail;
use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\CustomerConsent;
use App\Modules\Booking\Models\Service;
use App\Notifications\AppNotice;
use App\Services\Booking\LookPhotoService;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * The three follow-ups to saved looks: a consent record (incl. client opt-in
 * to sharing a look), "time to rebook" reminders, and quotes from photos.
 */
class ConsentRebookQuotesTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private Service $service;
    private Customer $customer;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(PermissionRoleSeeder::class);

        $this->tenant = Tenant::create(['name' => 'Kasi Braids', 'slug' => 'kasi-braids', 'email' => 'kb@example.com', 'is_active' => true]);
        $this->tenant->activateModule('booking');
        TenantContext::set($this->tenant->id);

        $this->service  = $this->service('Knotless Braids', price: 900, rebookAfter: 42, quote: false);
        $this->customer = $this->customer('lindiwe@example.com');

        $this->staff = User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
        $this->staff->assignRole('employee');
    }

    private function service(string $name, float $price = 500, ?int $rebookAfter = null, bool $quote = false, int $minutes = 1440): Service
    {
        return Service::create([
            'tenant_id' => $this->tenant->id, 'name' => $name, 'duration_minutes' => $minutes,
            'price' => $price, 'pricing_type' => 'flat', 'is_active' => true,
            'accepts_inspiration_photos' => true, 'rebook_after_days' => $rebookAfter, 'requires_quote' => $quote,
        ]);
    }

    private function customer(string $email): Customer
    {
        return Customer::create(['tenant_id' => $this->tenant->id, 'name' => 'Lindiwe', 'email' => $email, 'is_active' => true]);
    }

    private function appointment(Carbon $at, string $status = 'completed', ?Service $service = null, ?Customer $customer = null, int $minutes = 60): Appointment
    {
        $service ??= $this->service;
        $appt = Appointment::create([
            'tenant_id' => $this->tenant->id, 'customer_id' => ($customer ?? $this->customer)->id,
            'scheduled_at' => $at, 'duration_minutes' => $minutes, 'status' => $status,
        ]);
        $appt->services()->attach($service->id, [
            'duration_minutes' => $minutes, 'price_at_booking' => $service->price, 'quantity' => 1, 'sort_order' => 0,
        ]);

        return $appt;
    }

    private function asStaff(): static
    {
        TenantContext::clear();

        return $this->actingAs($this->staff);
    }

    private function savedLook(): Appointment
    {
        $appt = $this->appointment(now()->subWeek());
        app(LookPhotoService::class)->store($appt, [UploadedFile::fake()->image('after.jpg', 600, 600)], 'result');
        $this->asStaff()->post(route('appointments.look.save', $appt))->assertSessionHas('success');

        return $appt->fresh();
    }

    // ── Consent record ──────────────────────────────────────────────────────

    public function test_saving_and_removing_a_look_is_recorded_and_shown_on_the_customer_record(): void
    {
        $look = $this->savedLook();

        $this->actingAs($this->customer, 'customer')
            ->delete(route('book.looks.forget', [$this->tenant->slug, $look]))->assertSessionHas('success');

        $history = CustomerConsent::withoutGlobalScopes()->where('customer_id', $this->customer->id)->orderBy('id')->get();
        $this->assertSame([['look_saved', 'notified', 'staff'], ['look_saved', 'withdrawn', 'client']],
            $history->map(fn ($c) => [$c->scope, $c->action, $c->via])->all());
        $this->assertNotNull($history->last()->ip, 'client actions keep the IP as evidence');

        $this->asStaff()->get(route('customers.show', $this->customer))
            ->assertOk()->assertSee('Consent history')->assertSee('Saved look: client withdrew');
    }

    public function test_the_client_decides_whether_a_look_may_be_shared(): void
    {
        $look = $this->savedLook();
        $url  = route('book.looks.showcase', [$this->tenant->slug, $look]);

        $this->asStaff()->get(route('appointments.show', $look))->assertSee('Not cleared for sharing');

        $this->actingAs($this->customer, 'customer')->post($url, ['allow' => 1])->assertSessionHas('success');
        $this->assertNotNull($look->fresh()->look_showcase_at);
        $this->asStaff()->get(route('appointments.show', $look))->assertSee('OK to share');

        $this->actingAs($this->customer, 'customer')->post($url, ['allow' => 0]);
        $this->assertNull($look->fresh()->look_showcase_at);

        $actions = CustomerConsent::withoutGlobalScopes()->where('scope', 'look_showcase')->orderBy('id')->pluck('action')->all();
        $this->assertSame(['granted', 'withdrawn'], $actions);

        // Someone else can't flip it.
        $stranger = $this->customer('someone@example.com');
        $this->actingAs($stranger, 'customer')->post($url, ['allow' => 1])->assertNotFound();
        $this->assertNull($look->fresh()->look_showcase_at);
    }

    public function test_removing_a_look_also_withdraws_sharing(): void
    {
        $look = $this->savedLook();
        $this->actingAs($this->customer, 'customer')->post(route('book.looks.showcase', [$this->tenant->slug, $look]), ['allow' => 1]);

        $this->actingAs($this->customer, 'customer')->delete(route('book.looks.forget', [$this->tenant->slug, $look]));

        $this->assertNull($look->fresh()->look_showcase_at);
    }

    // ── Rebook reminders ────────────────────────────────────────────────────

    public function test_a_reminder_goes_out_once_when_the_service_is_due_again(): void
    {
        Notification::fake();
        Mail::fake();
        $appt = $this->appointment(now()->subDays(43));

        TenantContext::clear();
        $this->artisan('booking:send-rebook-reminders')->assertSuccessful();
        $this->artisan('booking:send-rebook-reminders')->assertSuccessful();

        Notification::assertSentToTimes($this->customer, AppNotice::class, 1);
        Mail::assertQueued(RebookReminderEmail::class, 1);
        $this->assertNotNull($appt->fresh()->rebook_reminded_at);
    }

    public function test_no_reminder_before_the_interval_or_for_services_without_one(): void
    {
        Notification::fake();
        $notYet  = $this->appointment(now()->subDays(10));
        $noRule  = $this->appointment(now()->subDays(60), service: $this->service('Consultation'));

        TenantContext::clear();
        $this->artisan('booking:send-rebook-reminders');

        Notification::assertNothingSent();
        $this->assertNull($notYet->fresh()->rebook_reminded_at, 'checked again on a later day');
        $this->assertNull($noRule->fresh()->rebook_reminded_at);
    }

    public function test_no_reminder_when_already_rebooked_opted_out_or_long_overdue(): void
    {
        Notification::fake();
        $rebooked = $this->appointment(now()->subDays(43));
        $this->appointment(now()->addDays(3), status: 'confirmed'); // already booked again

        $optedOut = $this->customer('optout@example.com');
        $optedOut->update(['rebook_reminders_opt_out_at' => now()]);
        $quiet = $this->appointment(now()->subDays(43), customer: $optedOut);

        $staleCustomer = $this->customer('old@example.com');
        $stale = $this->appointment(now()->subDays(42 + 30), customer: $staleCustomer);

        TenantContext::clear();
        $this->artisan('booking:send-rebook-reminders');

        Notification::assertNothingSent();
        foreach ([$rebooked, $quiet, $stale] as $appt) {
            $this->assertNotNull($appt->fresh()->rebook_reminded_at, 'looked at once, then left alone');
        }
    }

    public function test_the_email_links_to_the_saved_look_and_has_a_one_click_opt_out(): void
    {
        $look = $this->savedLook();
        $look->forceFill(['scheduled_at' => now()->subDays(43)])->saveQuietly();
        $look->load(['services', 'customer', 'tenant', 'lookPhotos']);

        $html = (new RebookReminderEmail($look, route('book.my-bookings', $this->tenant->slug)))->render();

        $this->assertStringContainsString('Book this look again', $html);
        $this->assertStringContainsString('Stop rebook reminders', $html);
        $this->assertStringContainsString('/book/kasi-braids/rebook-reminders/off/' . $this->customer->id, $html);
    }

    public function test_the_opt_out_link_works_without_login_but_only_when_signed(): void
    {
        $signed = URL::signedRoute('book.rebook-reminders.opt-out', [$this->tenant->slug, $this->customer->id]);

        $this->get(strtok($signed, '?'))->assertForbidden();
        $this->get($signed)->assertOk()->assertSee('Rebook reminders are off');

        $this->assertNotNull($this->customer->fresh()->rebook_reminders_opt_out_at);
        $this->assertSame('withdrawn', CustomerConsent::withoutGlobalScopes()->where('scope', 'rebook_reminders')->value('action'));
    }

    public function test_clients_can_switch_reminders_off_and_on_from_my_bookings(): void
    {
        $url = route('book.rebook-reminders.toggle', $this->tenant->slug);

        $this->actingAs($this->customer, 'customer')->get(route('book.my-bookings', $this->tenant->slug))
            ->assertSee('Rebook reminders are on');

        $this->actingAs($this->customer, 'customer')->post($url, ['on' => 0]);
        $this->assertNotNull($this->customer->fresh()->rebook_reminders_opt_out_at);

        $this->actingAs($this->customer, 'customer')->post($url, ['on' => 1]);
        $this->assertNull($this->customer->fresh()->rebook_reminders_opt_out_at);
    }

    public function test_owners_set_the_interval_and_quote_rule_per_service(): void
    {
        $owner = User::factory()->create(['tenant_id' => $this->tenant->id]);
        $owner->assignRole('tenant-owner');
        TenantContext::clear();

        $this->actingAs($owner)->put(route('services.update', $this->service), [
            'name' => 'Knotless Braids', 'duration_minutes' => 360, 'pricing_type' => 'flat', 'price' => 900,
            'is_active' => 1, 'accepts_inspiration_photos' => 1, 'rebook_after_days' => 49, 'requires_quote' => 1,
        ])->assertRedirect();

        $fresh = $this->service->fresh();
        $this->assertSame(49, $fresh->rebook_after_days);
        $this->assertTrue($fresh->requires_quote);
    }

    // ── Quotes from photos ──────────────────────────────────────────────────

    private function quotedBooking(): Appointment
    {
        $braids = $this->service('Waist-length Knotless', price: 900, quote: true);
        $this->actingAs($this->customer, 'customer');
        session(['pending_booking' => [
            'service_ids' => [$braids->id], 'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i'),
            'combo_id' => null, 'quantities' => [],
        ]]);
        $this->post(route('book.store', $this->tenant->slug), [
            'inspiration_photos' => [UploadedFile::fake()->image('want.jpg', 800, 800)],
        ])->assertRedirect();

        return Appointment::latest('id')->firstOrFail();
    }

    public function test_booking_a_quote_service_holds_the_slot_and_asks_staff_for_a_quote(): void
    {
        Notification::fake();

        $appt = $this->quotedBooking();

        $this->assertSame('requested', $appt->quote_status);
        $this->assertSame('pending', $appt->status);
        Notification::assertSentTo($this->staff, AppNotice::class, fn (AppNotice $n) => $n->toArray($this->staff)['title'] === 'Quote needed');
    }

    public function test_the_confirm_step_explains_the_quote(): void
    {
        $braids = $this->service('Waist-length Knotless', quote: true);

        $this->actingAs($this->customer, 'customer')
            ->get(route('book.confirm', ['slug' => $this->tenant->slug, 'service_ids' => [$braids->id], 'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i')]))
            ->assertOk()->assertSee('Price and time are confirmed from your photos');
    }

    public function test_staff_send_a_quote_and_the_client_hears_about_it(): void
    {
        Notification::fake();
        Mail::fake();
        $appt = $this->quotedBooking();

        $this->asStaff()->post(route('appointments.quote.send', $appt), [
            'quoted_price' => 1450, 'quoted_duration_minutes' => 1500, 'quote_note' => 'Waist length, beads included.',
        ])->assertSessionHas('success');

        $appt->refresh();
        $this->assertSame('sent', $appt->quote_status);
        $this->assertSame('1450.00', $appt->quoted_price);
        Notification::assertSentTo($this->customer, AppNotice::class, fn (AppNotice $n) => $n->toArray($this->customer)['title'] === 'Your quote is ready');
        Mail::assertQueued(QuoteReadyEmail::class);

        $this->actingAs($this->customer, 'customer')->get(route('book.my-bookings', $this->tenant->slug))
            ->assertSee('Your quote is ready')->assertSee('R1,450.00')->assertSee('Accept quote');
    }

    public function test_accepting_writes_the_quote_into_the_booking_price_and_time(): void
    {
        $appt = $this->quotedBooking();
        $this->asStaff()->post(route('appointments.quote.send', $appt), ['quoted_price' => 1450, 'quoted_duration_minutes' => 1500]);

        $this->actingAs($this->customer, 'customer')
            ->post(route('book.quote.accept', [$this->tenant->slug, $appt]))->assertSessionHas('success');

        $appt->refresh()->load('services');
        $this->assertSame('accepted', $appt->quote_status);
        $this->assertSame(1500, $appt->duration_minutes, 'calendar blocks the quoted time');
        $this->assertEqualsWithDelta(1450.0, (float) $appt->services->sum(fn ($s) => $s->pivot->price_at_booking), 0.001, 'POS checkout charges the quote');
    }

    public function test_declining_cancels_the_booking(): void
    {
        $appt = $this->quotedBooking();
        $this->asStaff()->post(route('appointments.quote.send', $appt), ['quoted_price' => 1450, 'quoted_duration_minutes' => 1500]);

        $this->actingAs($this->customer, 'customer')->post(route('book.quote.decline', [$this->tenant->slug, $appt]));

        $appt->refresh();
        $this->assertSame('declined', $appt->quote_status);
        $this->assertSame('cancelled', $appt->status);
    }

    public function test_quotes_are_guarded(): void
    {
        $appt = $this->quotedBooking();

        // Can't accept before a quote has been sent.
        $this->actingAs($this->customer, 'customer')->post(route('book.quote.accept', [$this->tenant->slug, $appt]))
            ->assertSessionHasErrors('quote');

        $this->asStaff()->post(route('appointments.quote.send', $appt), ['quoted_price' => 1450, 'quoted_duration_minutes' => 1500]);

        // Another client can't answer it.
        $stranger = $this->customer('someone@example.com');
        $this->actingAs($stranger, 'customer')->post(route('book.quote.accept', [$this->tenant->slug, $appt]))->assertNotFound();

        // Another business can't quote it.
        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@example.com', 'is_active' => true]);
        $other->activateModule('booking');
        $outsider = User::factory()->create(['tenant_id' => $other->id]);
        $outsider->assignRole('tenant-owner');
        TenantContext::clear();
        $this->actingAs($outsider)->post(route('appointments.quote.send', $appt), ['quoted_price' => 1, 'quoted_duration_minutes' => 60])
            ->assertNotFound();

        $this->assertSame('1450.00', $appt->fresh()->quoted_price);
    }

    public function test_staff_see_the_quote_panel(): void
    {
        $appt = $this->quotedBooking();

        $this->asStaff()->get(route('appointments.show', $appt))
            ->assertOk()->assertSee('Waiting for your quote')->assertSee('Send quote');
    }
}
