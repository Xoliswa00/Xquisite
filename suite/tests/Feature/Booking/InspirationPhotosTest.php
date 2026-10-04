<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentInspirationPhoto;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Database\Seeders\PermissionRoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Tests\TestCase;

/**
 * Customer "the look I want" photos: attached at booking or later from
 * My Bookings, stored privately, cleaned of EXIF, visible only to that
 * customer and the business's own staff, and pruned after 90 days.
 */
class InspirationPhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function makeTenant(string $slug = 'inspo-salon'): Tenant
    {
        return Tenant::create(['name' => 'Inspo Salon', 'slug' => $slug, 'email' => "{$slug}@example.com", 'is_active' => true]);
    }

    /** Multi-day duration skips the slot-availability check (same trick as BookingTermsAcceptanceTest). */
    private function makeService(Tenant $tenant, bool $accepts = true): Service
    {
        TenantContext::set($tenant->id);

        return Service::create([
            'tenant_id'                  => $tenant->id,
            'name'                       => 'Bridal Nails',
            'duration_minutes'           => 1440,
            'price'                      => 500,
            'pricing_type'               => 'flat',
            'is_active'                  => true,
            'accepts_inspiration_photos' => $accepts,
        ]);
    }

    private function makeCustomer(Tenant $tenant, string $email = 'jane@example.com'): Customer
    {
        return Customer::create(['tenant_id' => $tenant->id, 'name' => 'Jane Client', 'email' => $email, 'is_active' => true]);
    }

    private function makeAppointment(Tenant $tenant, Customer $customer, Service $service, ?Carbon $at = null, string $status = 'pending'): Appointment
    {
        $appt = Appointment::create([
            'tenant_id'        => $tenant->id,
            'customer_id'      => $customer->id,
            'scheduled_at'     => $at ?? now()->addDays(2),
            'duration_minutes' => 60,
            'status'           => $status,
        ]);
        $appt->services()->attach($service->id, ['duration_minutes' => 60, 'price_at_booking' => 500, 'quantity' => 1, 'sort_order' => 0]);

        return $appt;
    }

    private function book(Tenant $tenant, Customer $customer, Service $service, array $extra = [])
    {
        $this->actingAs($customer, 'customer');
        session(['pending_booking' => [
            'service_ids'  => [$service->id],
            'scheduled_at' => Carbon::tomorrow()->format('Y-m-d H:i'),
            'combo_id'     => null,
            'quantities'   => [],
        ]]);

        return $this->post(route('book.store', $tenant->slug), $extra);
    }

    private function photo(string $name = 'look.jpg', int $w = 2400, int $h = 1800): UploadedFile
    {
        return UploadedFile::fake()->image($name, $w, $h);
    }

    /** A real JPEG with an EXIF APP1 segment spliced in after SOI, carrying a fake GPS marker. */
    private function photoWithExif(): UploadedFile
    {
        $jpeg    = (string) ImageManager::gd()->create(800, 600)->fill('cc8899')->toJpeg();
        $payload = "Exif\0\0" . 'MM' . "\0\x2A\0\0\0\x08" . 'GPS-SECRET-LOCATION';
        $app1    = "\xFF\xE1" . pack('n', strlen($payload) + 2) . $payload;

        return UploadedFile::fake()->createWithContent('exif.jpg', substr($jpeg, 0, 2) . $app1 . substr($jpeg, 2));
    }

    // ── Booking step ────────────────────────────────────────────────────────

    public function test_photos_and_description_attach_to_a_new_booking_on_the_private_disk(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);

        $this->book($tenant, $customer, $service, [
            'inspiration_photos' => [$this->photo('a.jpg'), $this->photo('b.png', 600, 900)],
            'inspiration_notes'  => 'Almond shape, nude base',
        ])->assertRedirect()->assertSessionDoesntHaveErrors();

        $appt = Appointment::firstOrFail();
        $this->assertSame('Almond shape, nude base', $appt->inspiration_notes);
        $this->assertCount(2, $appt->inspirationPhotos);

        $first = $appt->inspirationPhotos->first();
        Storage::disk('local')->assertExists($first->path);
        Storage::disk('local')->assertExists($first->path_thumb);
        $this->assertSame([], Storage::disk('public')->allFiles(), 'Nothing may land on the public disk');

        // Downscaled to the 1600px edge, aspect kept; thumb is a 400 square.
        $this->assertSame(1600, $first->width);
        $this->assertSame(1200, $first->height);
        $thumb = ImageManager::gd()->read(Storage::disk('local')->get($first->path_thumb));
        $this->assertSame([400, 400], [$thumb->width(), $thumb->height()]);
    }

    public function test_exif_metadata_never_reaches_disk(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);

        $upload = $this->photoWithExif();
        $this->assertStringContainsString('GPS-SECRET-LOCATION', file_get_contents($upload->getRealPath()));

        $this->book($tenant, $customer, $service, ['inspiration_photos' => [$upload]])->assertRedirect();

        $photo = AppointmentInspirationPhoto::firstOrFail();
        foreach ($photo->storagePaths() as $path) {
            $bytes = Storage::disk('local')->get($path);
            $this->assertStringNotContainsString('GPS-SECRET-LOCATION', $bytes);
            $this->assertStringNotContainsString('Exif', $bytes);
        }
    }

    public function test_booking_without_photos_still_works(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);

        $this->book($tenant, $customer, $service)->assertRedirect()->assertSessionDoesntHaveErrors();

        $this->assertSame(1, Appointment::count());
        $this->assertSame(0, AppointmentInspirationPhoto::count());
    }

    public function test_more_than_three_photos_is_rejected_and_no_booking_is_made(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);

        $this->book($tenant, $customer, $service, [
            'inspiration_photos' => [$this->photo(), $this->photo(), $this->photo(), $this->photo()],
        ])->assertSessionHasErrors('inspiration_photos');

        $this->assertSame(0, Appointment::count());
    }

    public function test_non_image_upload_is_rejected(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);

        $this->book($tenant, $customer, $service, [
            'inspiration_photos' => [UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')],
        ])->assertSessionHasErrors('inspiration_photos.0');

        $this->assertSame(0, Appointment::count());
    }

    public function test_photos_are_ignored_when_the_service_has_the_upload_switched_off(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant, accepts: false);
        $customer = $this->makeCustomer($tenant);

        $this->book($tenant, $customer, $service, [
            'inspiration_photos' => [$this->photo()],
            'inspiration_notes'  => 'should be dropped',
        ])->assertRedirect();

        $this->assertSame(1, Appointment::count());
        $this->assertNull(Appointment::first()->inspiration_notes);
        $this->assertSame(0, AppointmentInspirationPhoto::count());
    }

    // ── Who can see them ────────────────────────────────────────────────────

    public function test_only_the_booking_customer_can_view_their_photos(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $owner    = $this->makeCustomer($tenant);
        $stranger = $this->makeCustomer($tenant, 'other@example.com');
        $this->book($tenant, $owner, $service, ['inspiration_photos' => [$this->photo()]]);

        $photo = AppointmentInspirationPhoto::firstOrFail();
        $url   = route('book.inspiration.show', [$tenant->slug, $photo->appointment_id, $photo->id, 'thumb']);

        $this->actingAs($owner, 'customer')->get($url)
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=3600, private');

        $this->actingAs($stranger, 'customer')->get($url)->assertNotFound();
    }

    public function test_a_guest_cannot_view_photos(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $this->book($tenant, $customer, $service, ['inspiration_photos' => [$this->photo()]]);
        auth('customer')->logout();

        $photo = AppointmentInspirationPhoto::firstOrFail();

        $this->get(route('book.inspiration.show', [$tenant->slug, $photo->appointment_id, $photo->id, 'full']))
            ->assertRedirect();
    }

    public function test_staff_see_their_own_tenants_photos_but_not_another_tenants(): void
    {
        $this->seed(PermissionRoleSeeder::class);

        $tenant   = $this->makeTenant();
        $tenant->activateModule('booking');
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $this->book($tenant, $customer, $service, ['inspiration_photos' => [$this->photo()]]);
        $photo = AppointmentInspirationPhoto::firstOrFail();

        $staff = User::factory()->create(['tenant_id' => $tenant->id]);
        $staff->assignRole('employee');

        $other = $this->makeTenant('other-salon');
        $other->activateModule('booking');
        $outsider = User::factory()->create(['tenant_id' => $other->id]);
        $outsider->assignRole('tenant-owner');

        TenantContext::clear();
        $this->actingAs($staff)->get($photo->staffUrl())->assertOk();

        TenantContext::clear();
        $this->actingAs($outsider)->get($photo->staffUrl())->assertNotFound();
    }

    public function test_staff_appointment_page_shows_the_photos_and_description(): void
    {
        $this->seed(PermissionRoleSeeder::class);

        $tenant   = $this->makeTenant();
        $tenant->activateModule('booking');
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $this->book($tenant, $customer, $service, [
            'inspiration_photos' => [$this->photo()],
            'inspiration_notes'  => 'Chrome finish please',
        ]);
        $appt  = Appointment::firstOrFail();
        $photo = $appt->inspirationPhotos->first();

        $staff = User::factory()->create(['tenant_id' => $tenant->id]);
        $staff->assignRole('employee');

        TenantContext::clear();
        $this->actingAs($staff)->get(route('appointments.show', $appt))
            ->assertOk()
            ->assertSee('The look they want')
            ->assertSee('Chrome finish please')
            ->assertSee($photo->staffUrl('thumb'), false);
    }

    // ── Adding / removing later from My Bookings ────────────────────────────

    public function test_customer_can_add_photos_after_booking_up_to_the_cap(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $appt     = $this->makeAppointment($tenant, $customer, $service);
        $url      = route('book.inspiration.store', [$tenant->slug, $appt]);

        $this->actingAs($customer, 'customer')
            ->post($url, ['inspiration_photos' => [$this->photo(), $this->photo()], 'inspiration_notes' => 'Like these'])
            ->assertSessionHas('success');
        $this->assertSame(2, $appt->inspirationPhotos()->count());
        $this->assertSame('Like these', $appt->fresh()->inspiration_notes);

        // Two already attached, so only one slot is left.
        $this->actingAs($customer, 'customer')
            ->post($url, ['inspiration_photos' => [$this->photo(), $this->photo()]])
            ->assertSessionHasErrors('inspiration_photos');
        $this->assertSame(2, $appt->inspirationPhotos()->count());

        $this->actingAs($customer, 'customer')
            ->post($url, ['inspiration_photos' => [$this->photo()]])
            ->assertSessionHas('success');
        $this->assertSame(3, $appt->inspirationPhotos()->count());
    }

    public function test_customer_cannot_add_photos_to_someone_elses_booking(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $owner    = $this->makeCustomer($tenant);
        $stranger = $this->makeCustomer($tenant, 'other@example.com');
        $appt     = $this->makeAppointment($tenant, $owner, $service);

        $this->actingAs($stranger, 'customer')
            ->post(route('book.inspiration.store', [$tenant->slug, $appt]), ['inspiration_photos' => [$this->photo()]])
            ->assertNotFound();

        $this->assertSame(0, AppointmentInspirationPhoto::count());
    }

    public function test_photos_are_locked_once_the_appointment_has_passed(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $appt     = $this->makeAppointment($tenant, $customer, $service, now()->subDay(), 'completed');

        $this->actingAs($customer, 'customer')
            ->post(route('book.inspiration.store', [$tenant->slug, $appt]), ['inspiration_photos' => [$this->photo()]])
            ->assertSessionHasErrors('inspiration_photos');

        $this->assertSame(0, AppointmentInspirationPhoto::count());
    }

    public function test_removing_a_photo_deletes_its_files(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $appt     = $this->makeAppointment($tenant, $customer, $service);

        $this->actingAs($customer, 'customer')
            ->post(route('book.inspiration.store', [$tenant->slug, $appt]), ['inspiration_photos' => [$this->photo()]]);
        $photo = AppointmentInspirationPhoto::firstOrFail();
        $paths = $photo->storagePaths();

        $this->actingAs($customer, 'customer')
            ->delete(route('book.inspiration.destroy', [$tenant->slug, $appt, $photo]))
            ->assertSessionHas('success');

        $this->assertSame(0, AppointmentInspirationPhoto::count());
        foreach ($paths as $path) {
            Storage::disk('local')->assertMissing($path);
        }
    }

    public function test_my_bookings_page_renders_the_inspiration_section(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $this->makeAppointment($tenant, $customer, $service);

        $this->actingAs($customer, 'customer')
            ->get(route('book.my-bookings', $tenant->slug))
            ->assertOk()
            ->assertSee('Add the look you want');
    }

    // ── Retention ───────────────────────────────────────────────────────────

    public function test_prune_removes_photos_90_days_after_the_appointment_and_keeps_recent_ones(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $old      = $this->makeAppointment($tenant, $customer, $service);
        $recent   = $this->makeAppointment($tenant, $customer, $service);

        $this->actingAs($customer, 'customer');
        $this->post(route('book.inspiration.store', [$tenant->slug, $old]), ['inspiration_photos' => [$this->photo()]]);
        $this->post(route('book.inspiration.store', [$tenant->slug, $recent]), ['inspiration_photos' => [$this->photo()]]);

        $old->forceFill(['scheduled_at' => now()->subDays(91)])->saveQuietly();
        $recent->forceFill(['scheduled_at' => now()->subDays(10)])->saveQuietly();
        $oldPaths = $old->inspirationPhotos()->first()->storagePaths();

        TenantContext::clear();
        $this->artisan('booking:prune-inspiration-photos')->assertSuccessful();

        $this->assertSame(0, $old->inspirationPhotos()->count());
        $this->assertSame(1, $recent->inspirationPhotos()->count());
        foreach ($oldPaths as $path) {
            Storage::disk('local')->assertMissing($path);
        }
    }

    public function test_prune_sweeps_folders_left_by_a_force_deleted_appointment(): void
    {
        Storage::disk('local')->put('inspiration/1/999999/x.webp', 'x');

        $this->artisan('booking:prune-inspiration-photos')->assertSuccessful();

        $this->assertSame([], Storage::disk('local')->allFiles('inspiration'));
    }
}
