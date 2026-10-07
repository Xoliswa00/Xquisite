<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentLookPhoto;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Notifications\AppNotice;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Follow-ups to inspiration photos: staff are told when a client adds photos
 * after booking, staff can add "after" photos and save the client's look,
 * the client can book that look again (photos copied onto the new booking)
 * or remove it, and saved looks are kept 2 years instead of 90 days.
 */
class SavedLooksTest extends TestCase
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

        $this->tenant = Tenant::create(['name' => 'Looks Salon', 'slug' => 'looks-salon', 'email' => 'looks@example.com', 'is_active' => true]);
        $this->tenant->activateModule('booking');
        TenantContext::set($this->tenant->id);

        $this->service = Service::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Knotless Braids', 'duration_minutes' => 1440,
            'price' => 900, 'pricing_type' => 'flat', 'is_active' => true, 'accepts_inspiration_photos' => true,
        ]);
        $this->customer = $this->customer('thandi@example.com');

        $this->staff = User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
        $this->staff->assignRole('employee');
    }

    private function customer(string $email): Customer
    {
        return Customer::create(['tenant_id' => $this->tenant->id, 'name' => 'Thandi', 'email' => $email, 'is_active' => true]);
    }

    private function appointment(?Customer $customer = null, ?Carbon $at = null, string $status = 'pending'): Appointment
    {
        $appt = Appointment::create([
            'tenant_id' => $this->tenant->id, 'customer_id' => ($customer ?? $this->customer)->id,
            'scheduled_at' => $at ?? now()->addDays(2), 'duration_minutes' => 60, 'status' => $status,
        ]);
        $appt->services()->attach($this->service->id, ['duration_minutes' => 60, 'price_at_booking' => 900, 'quantity' => 1, 'sort_order' => 0]);

        return $appt;
    }

    private function photo(): UploadedFile
    {
        return UploadedFile::fake()->image('look.jpg', 900, 900);
    }

    private function asStaff(): static
    {
        TenantContext::clear();

        return $this->actingAs($this->staff);
    }

    /** A completed booking with one client inspiration photo and one staff after photo, saved as the look. */
    private function savedLook(): Appointment
    {
        $appt = $this->appointment(at: now()->subWeek(), status: 'completed');
        app(\App\Services\Booking\LookPhotoService::class)->store($appt, [$this->photo()]);
        $appt->update(['inspiration_notes' => 'Waist length, 1B']);

        $this->asStaff()->post(route('appointments.look.results.store', $appt), ['result_photos' => [$this->photo()]]);
        $this->asStaff()->post(route('appointments.look.save', $appt))->assertSessionHas('success');

        return $appt->fresh();
    }

    // ── Notify on later add ─────────────────────────────────────────────────

    public function test_staff_are_notified_when_a_client_adds_photos_after_booking(): void
    {
        Notification::fake();
        $appt = $this->appointment();

        $this->actingAs($this->customer, 'customer')
            ->post(route('book.inspiration.store', [$this->tenant->slug, $appt]), ['inspiration_photos' => [$this->photo(), $this->photo()]])
            ->assertSessionHas('success');

        Notification::assertSentTo($this->staff, AppNotice::class, function (AppNotice $n) use ($appt) {
            $data = $n->toArray($this->staff);

            return str_contains($data['message'], 'added 2 inspiration photos')
                && $data['url'] === route('appointments.show', $appt);
        });
    }

    public function test_a_failed_upload_sends_no_notification(): void
    {
        Notification::fake();
        $appt = $this->appointment();

        $this->actingAs($this->customer, 'customer')
            ->post(route('book.inspiration.store', [$this->tenant->slug, $appt]), [
                'inspiration_photos' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')],
            ]);

        Notification::assertNothingSent();
    }

    // ── After photos + saving the look ──────────────────────────────────────

    public function test_staff_add_after_photos_and_save_the_look(): void
    {
        $appt = $this->savedLook();

        $this->assertTrue($appt->isLookSaved());
        $this->assertSame(1, $appt->resultPhotos()->count());
        $this->assertSame(1, $appt->inspirationPhotos()->count());

        $this->asStaff()->get(route('appointments.show', $appt))
            ->assertOk()->assertSee('How it turned out')->assertSee("Saved to Thandi's looks", false);

        $this->asStaff()->get(route('customers.show', $this->customer))
            ->assertOk()->assertSee('Saved looks')->assertSee('Waist length, 1B');
    }

    public function test_a_look_needs_an_after_photo_before_it_can_be_saved(): void
    {
        $appt = $this->appointment(status: 'completed');
        // The client's own screenshot alone isn't a look worth keeping 2 years.
        app(\App\Services\Booking\LookPhotoService::class)->store($appt, [$this->photo()]);

        $this->asStaff()->post(route('appointments.look.save', $appt))->assertSessionHasErrors('look');
        $this->assertNull($appt->fresh()->look_saved_at);
    }

    public function test_client_and_staff_can_each_only_delete_their_own_kind_of_photo(): void
    {
        $appt   = $this->savedLook();
        $result = $appt->resultPhotos()->first();
        $inspo  = $appt->inspirationPhotos()->first();
        $appt->update(['scheduled_at' => now()->addDay(), 'status' => 'confirmed']); // still editable for the client

        $this->actingAs($this->customer, 'customer')
            ->delete(route('book.inspiration.destroy', [$this->tenant->slug, $appt, $result]))->assertNotFound();
        $this->asStaff()
            ->delete(route('appointments.look.results.destroy', [$appt, $inspo]))->assertNotFound();

        $this->asStaff()
            ->delete(route('appointments.look.results.destroy', [$appt, $result]))->assertSessionHas('success');
        $this->assertSame(0, $appt->resultPhotos()->count());
    }

    public function test_staff_from_another_business_cannot_touch_the_look(): void
    {
        $appt  = $this->savedLook();
        $other = Tenant::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@example.com', 'is_active' => true]);
        $other->activateModule('booking');
        $outsider = User::factory()->create(['tenant_id' => $other->id]);
        $outsider->assignRole('tenant-owner');

        TenantContext::clear();
        $this->actingAs($outsider)->delete(route('appointments.look.forget', $appt))->assertNotFound();
        $this->assertTrue($appt->fresh()->isLookSaved());
    }

    // ── Client side ─────────────────────────────────────────────────────────

    public function test_client_sees_saved_looks_and_removing_one_deletes_the_after_photos_and_blocks_resaving(): void
    {
        $appt = $this->savedLook();
        $resultPaths = $appt->resultPhotos()->get()->flatMap->storagePaths()->all();

        $this->actingAs($this->customer, 'customer')
            ->get(route('book.my-bookings', $this->tenant->slug))
            ->assertOk()->assertSee('Your saved looks')->assertSee('Book this look again');

        $stranger = $this->customer('someone@example.com');
        $this->actingAs($stranger, 'customer')
            ->delete(route('book.looks.forget', [$this->tenant->slug, $appt]))->assertNotFound();

        $this->actingAs($this->customer, 'customer')
            ->delete(route('book.looks.forget', [$this->tenant->slug, $appt]))->assertSessionHas('success');

        $appt->refresh();
        $this->assertNull($appt->look_saved_at);
        $this->assertNotNull($appt->look_removed_at);
        $this->assertSame(0, $appt->resultPhotos()->count(), 'the business\'s after photos of the client go immediately');
        $this->assertSame(1, $appt->inspirationPhotos()->count(), 'the client\'s own upload follows the normal 90-day rule');
        foreach ($resultPaths as $path) {
            Storage::disk('local')->assertMissing($path);
        }

        // Staff can't quietly save it again.
        $this->asStaff()->post(route('appointments.look.results.store', $appt), ['result_photos' => [$this->photo()]]);
        $this->asStaff()->post(route('appointments.look.save', $appt))->assertSessionHasErrors('look');
        $this->assertNull($appt->fresh()->look_saved_at);
    }

    public function test_the_client_is_told_when_their_look_is_saved(): void
    {
        Notification::fake();

        $appt = $this->savedLook();

        Notification::assertSentTo($this->customer, AppNotice::class, function (AppNotice $n) use ($appt) {
            $message = $n->toArray($this->customer)['message'];

            return str_contains($message, 'saved your look from ' . $appt->scheduled_at->format('d M Y'))
                && str_contains($message, 'remove it any time');
        });
    }

    public function test_after_photos_and_saving_wait_until_the_appointment_has_happened(): void
    {
        $future = $this->appointment(at: now()->addDays(3), status: 'confirmed');

        $this->asStaff()->post(route('appointments.look.results.store', $future), ['result_photos' => [$this->photo()]])
            ->assertSessionHasErrors('result_photos');
        $this->assertSame(0, $future->resultPhotos()->count());

        $this->asStaff()->get(route('appointments.show', $future))
            ->assertOk()->assertDontSee('Add after photos')->assertDontSee("Save as client's look", false);
    }

    public function test_the_look_section_is_hidden_where_looks_dont_apply(): void
    {
        $plain = Service::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Wheel Alignment', 'duration_minutes' => 60,
            'price' => 400, 'pricing_type' => 'flat', 'is_active' => true, 'accepts_inspiration_photos' => false,
        ]);
        $appt = Appointment::create([
            'tenant_id' => $this->tenant->id, 'customer_id' => $this->customer->id,
            'scheduled_at' => now()->subDay(), 'duration_minutes' => 60, 'status' => 'completed',
        ]);
        $appt->services()->attach($plain->id, ['duration_minutes' => 60, 'price_at_booking' => 400, 'quantity' => 1, 'sort_order' => 0]);

        $this->asStaff()->get(route('appointments.show', $appt))
            ->assertOk()->assertDontSee('The look they want')->assertDontSee('Add after photos');
    }

    public function test_book_this_look_again_copies_the_look_onto_the_new_booking(): void
    {
        $look = $this->savedLook();

        $this->actingAs($this->customer, 'customer')
            ->post(route('book.looks.rebook', [$this->tenant->slug, $look]))
            ->assertRedirect(route('book.service', ['slug' => $this->tenant->slug, 'service_ids' => [$this->service->id]]));
        $this->assertSame($look->id, session('rebook_look.id'));

        $this->actingAs($this->customer, 'customer')
            ->get(route('book.confirm', ['slug' => $this->tenant->slug, 'service_ids' => [$this->service->id], 'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i')]))
            ->assertOk()->assertSee('Use your saved look from')->assertSee('After');

        $this->actingAs($this->customer, 'customer')
            ->post(route('book.store', $this->tenant->slug), ['use_saved_look' => '1'])
            ->assertRedirect();

        $new = Appointment::where('id', '!=', $look->id)->latest('id')->firstOrFail();
        $this->assertSame('Waist length, 1B', $new->inspiration_notes);
        $copies = $new->inspirationPhotos()->get();
        $this->assertCount(2, $copies);

        // Copies, not shared files: removing the old look can't break the new booking.
        $oldPaths = $look->lookPhotos()->pluck('path')->all();
        foreach ($copies as $copy) {
            $this->assertNotContains($copy->path, $oldPaths);
            Storage::disk('local')->assertExists($copy->path);
        }
        $this->assertNull(session('rebook_look'));
    }

    public function test_rebook_by_get_is_not_allowed(): void
    {
        $look = $this->savedLook();

        $this->actingAs($this->customer, 'customer')
            ->get('/book/' . $this->tenant->slug . '/looks/' . $look->id . '/book-again')
            ->assertStatus(405);
    }

    public function test_when_the_look_services_are_gone_the_look_is_still_attached_to_whatever_they_book(): void
    {
        $look = $this->savedLook();
        $this->service->update(['is_active' => false]);
        $other = Service::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Box Braids', 'duration_minutes' => 1440,
            'price' => 800, 'pricing_type' => 'flat', 'is_active' => true, 'accepts_inspiration_photos' => true,
        ]);

        $this->actingAs($this->customer, 'customer')
            ->post(route('book.looks.rebook', [$this->tenant->slug, $look]))
            ->assertRedirect(route('book.index', $this->tenant->slug))
            ->assertSessionHas('info', fn ($m) => str_contains($m, "aren't on offer any more"));

        $this->actingAs($this->customer, 'customer')
            ->get(route('book.confirm', ['slug' => $this->tenant->slug, 'service_ids' => [$other->id], 'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i')]))
            ->assertOk()->assertSee('Use your saved look from');

        $this->actingAs($this->customer, 'customer')
            ->post(route('book.store', $this->tenant->slug), ['use_saved_look' => '1'])->assertRedirect();

        $new = Appointment::where('id', '!=', $look->id)->latest('id')->firstOrFail();
        $this->assertSame(2, $new->inspirationPhotos()->count());
    }

    public function test_an_abandoned_rebook_does_not_turn_up_on_an_unrelated_booking(): void
    {
        $look  = $this->savedLook();
        $other = Service::create([
            'tenant_id' => $this->tenant->id, 'name' => 'Lash Lift', 'duration_minutes' => 1440,
            'price' => 300, 'pricing_type' => 'flat', 'is_active' => true, 'accepts_inspiration_photos' => true,
        ]);
        $this->actingAs($this->customer, 'customer')->post(route('book.looks.rebook', [$this->tenant->slug, $look]));

        // A different service: not offered.
        $this->actingAs($this->customer, 'customer')
            ->get(route('book.confirm', ['slug' => $this->tenant->slug, 'service_ids' => [$other->id], 'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i')]))
            ->assertOk()->assertDontSee('Use your saved look from');

        // The same service, but hours later: not offered either.
        $this->travel(3)->hours();
        $this->actingAs($this->customer, 'customer')
            ->get(route('book.confirm', ['slug' => $this->tenant->slug, 'service_ids' => [$this->service->id], 'scheduled_at' => Carbon::tomorrow()->addDay()->format('Y-m-d H:i')]))
            ->assertOk()->assertDontSee('Use your saved look from');
    }

    public function test_unticking_the_saved_look_books_without_it(): void
    {
        $look = $this->savedLook();
        $this->actingAs($this->customer, 'customer')->post(route('book.looks.rebook', [$this->tenant->slug, $look]));
        session(['pending_booking' => [
            'service_ids' => [$this->service->id], 'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i'),
            'combo_id' => null, 'quantities' => [],
        ]]);

        $this->actingAs($this->customer, 'customer')->post(route('book.store', $this->tenant->slug))->assertRedirect();

        $new = Appointment::where('id', '!=', $look->id)->latest('id')->firstOrFail();
        $this->assertSame(0, $new->lookPhotos()->count());
    }

    public function test_someone_elses_look_in_the_session_is_ignored(): void
    {
        $look     = $this->savedLook();
        $stranger = $this->customer('someone@example.com');

        $this->actingAs($stranger, 'customer')
            ->post(route('book.looks.rebook', [$this->tenant->slug, $look]))->assertNotFound();

        session([
            'rebook_look'     => ['id' => $look->id, 'service_ids' => [$this->service->id], 'at' => now()->timestamp],
            'pending_booking' => [
                'service_ids' => [$this->service->id], 'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i'),
                'combo_id' => null, 'quantities' => [],
            ],
        ]);
        $this->actingAs($stranger, 'customer')
            ->post(route('book.store', $this->tenant->slug), ['use_saved_look' => '1'])->assertRedirect();

        $new = Appointment::where('customer_id', $stranger->id)->firstOrFail();
        $this->assertSame(0, $new->lookPhotos()->count());
    }

    // ── Notification targeting ──────────────────────────────────────────────

    public function test_repeat_uploads_send_one_notice_not_one_per_photo(): void
    {
        Notification::fake();
        $appt    = $this->appointment();
        $service = app(\App\Services\Notifications\BookingNotificationService::class);

        // Called at the service level: in tests the app outlives a request, so
        // HTTP-level "after response" callbacks would re-run on the next request.
        $service->notifyInspirationAdded($appt, 1);
        $service->notifyInspirationAdded($appt, 1);
        $this->app->terminate();

        Notification::assertSentToTimes($this->staff, AppNotice::class, 1);

        // After the cooldown the gate reopens, so a later upload notifies again.
        $this->travel(\App\Services\Notifications\BookingNotificationService::INSPIRATION_NOTICE_COOLDOWN_MINUTES + 1)->minutes();
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has("inspiration-notice:{$appt->id}"));
    }

    public function test_an_assigned_booking_notifies_its_stylist_and_owners_not_every_employee(): void
    {
        Notification::fake();
        $stylistUser = User::factory()->create(['tenant_id' => $this->tenant->id, 'email' => 'thandeka@example.com', 'is_active' => true]);
        $stylistUser->assignRole('employee');
        $owner = User::factory()->create(['tenant_id' => $this->tenant->id, 'is_active' => true]);
        $owner->assignRole('tenant-owner');
        $staffRecord = \App\Modules\Booking\Models\Staff::create(['tenant_id' => $this->tenant->id, 'name' => 'Thandeka', 'email' => 'thandeka@example.com', 'is_active' => true]);

        $appt = $this->appointment();
        $appt->update(['staff_id' => $staffRecord->id]);

        $this->actingAs($this->customer, 'customer')
            ->post(route('book.inspiration.store', [$this->tenant->slug, $appt]), ['inspiration_photos' => [$this->photo()]]);

        Notification::assertSentTo($stylistUser, AppNotice::class);
        Notification::assertSentTo($owner, AppNotice::class);
        Notification::assertNotSentTo($this->staff, AppNotice::class); // the other employee
    }

    // ── Retention ───────────────────────────────────────────────────────────

    public function test_saved_looks_outlive_the_90_day_prune_but_not_2_years(): void
    {
        $saved   = $this->savedLook();
        $unsaved = $this->appointment(at: now()->subDays(100), status: 'completed');
        app(\App\Services\Booking\LookPhotoService::class)->store($unsaved, [$this->photo()]);

        $saved->forceFill(['scheduled_at' => now()->subDays(100)])->saveQuietly();
        TenantContext::clear();
        $this->artisan('booking:prune-look-photos')->assertSuccessful();

        $this->assertSame(2, $saved->lookPhotos()->count(), 'saved look kept past 90 days');
        $this->assertSame(0, $unsaved->lookPhotos()->count(), 'unsaved photos pruned at 90 days');

        $saved->forceFill(['scheduled_at' => now()->subDays(731)])->saveQuietly();
        $this->artisan('booking:prune-look-photos')->assertSuccessful();
        $this->assertSame(0, $saved->lookPhotos()->count(), 'saved look pruned after 2 years');
        $this->assertNull($saved->fresh()->look_saved_at, 'and no longer listed as an empty saved look');
    }
}
