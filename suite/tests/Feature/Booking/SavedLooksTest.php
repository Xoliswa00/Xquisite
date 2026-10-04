<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentInspirationPhoto;
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
        app(\App\Services\Booking\InspirationPhotoService::class)->store($appt, [$this->photo()]);
        $appt->update(['inspiration_notes' => 'Waist length, 1B']);

        $this->asStaff()->post(route('appointments.look.results.store', $appt), ['inspiration_photos' => [$this->photo()]]);
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

    public function test_a_look_needs_something_in_it_before_it_can_be_saved(): void
    {
        $appt = $this->appointment(status: 'completed');

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

    public function test_client_sees_saved_looks_and_can_remove_one(): void
    {
        $appt = $this->savedLook();

        $this->actingAs($this->customer, 'customer')
            ->get(route('book.my-bookings', $this->tenant->slug))
            ->assertOk()->assertSee('Your saved looks')->assertSee('Book this look again');

        $stranger = $this->customer('someone@example.com');
        $this->actingAs($stranger, 'customer')
            ->delete(route('book.looks.forget', [$this->tenant->slug, $appt]))->assertNotFound();

        $this->actingAs($this->customer, 'customer')
            ->delete(route('book.looks.forget', [$this->tenant->slug, $appt]))->assertSessionHas('success');
        $this->assertNull($appt->fresh()->look_saved_at);
    }

    public function test_book_this_look_again_copies_the_look_onto_the_new_booking(): void
    {
        $look = $this->savedLook();

        $this->actingAs($this->customer, 'customer')
            ->get(route('book.looks.rebook', [$this->tenant->slug, $look]))
            ->assertRedirect(route('book.service', ['slug' => $this->tenant->slug, 'service_ids' => [$this->service->id]]));
        $this->assertSame($look->id, session('rebook_look'));

        $this->actingAs($this->customer, 'customer')
            ->get(route('book.confirm', ['slug' => $this->tenant->slug, 'service_ids' => [$this->service->id], 'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i')]))
            ->assertOk()->assertSee('Use your saved look from');

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

    public function test_unticking_the_saved_look_books_without_it(): void
    {
        $look = $this->savedLook();
        $this->actingAs($this->customer, 'customer')->get(route('book.looks.rebook', [$this->tenant->slug, $look]));
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
            ->get(route('book.looks.rebook', [$this->tenant->slug, $look]))->assertNotFound();

        session(['rebook_look' => $look->id, 'pending_booking' => [
            'service_ids' => [$this->service->id], 'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i'),
            'combo_id' => null, 'quantities' => [],
        ]]);
        $this->actingAs($stranger, 'customer')
            ->post(route('book.store', $this->tenant->slug), ['use_saved_look' => '1'])->assertRedirect();

        $new = Appointment::where('customer_id', $stranger->id)->firstOrFail();
        $this->assertSame(0, $new->lookPhotos()->count());
    }

    // ── Retention ───────────────────────────────────────────────────────────

    public function test_saved_looks_outlive_the_90_day_prune_but_not_2_years(): void
    {
        $saved   = $this->savedLook();
        $unsaved = $this->appointment(at: now()->subDays(100), status: 'completed');
        app(\App\Services\Booking\InspirationPhotoService::class)->store($unsaved, [$this->photo()]);

        $saved->forceFill(['scheduled_at' => now()->subDays(100)])->saveQuietly();
        TenantContext::clear();
        $this->artisan('booking:prune-inspiration-photos')->assertSuccessful();

        $this->assertSame(2, $saved->lookPhotos()->count(), 'saved look kept past 90 days');
        $this->assertSame(0, $unsaved->lookPhotos()->count(), 'unsaved photos pruned at 90 days');

        $saved->forceFill(['scheduled_at' => now()->subDays(731)])->saveQuietly();
        $this->artisan('booking:prune-inspiration-photos')->assertSuccessful();
        $this->assertSame(0, $saved->lookPhotos()->count(), 'saved look pruned after 2 years');
    }
}
