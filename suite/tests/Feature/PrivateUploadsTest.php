<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Property\Models\Applicant;
use App\Modules\Property\Models\ApplicantDocument;
use App\Modules\Property\Models\Property;
use App\Services\Tenant\TenantContext;
use App\Support\PrivateFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Payment proofs, rental applicant documents and maintenance/inspection
 * photos must never be reachable at a public /storage URL; they're stored
 * privately and served only via short-lived signed links.
 */
class PrivateUploadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function tenant(): Tenant
    {
        $tenant = Tenant::create(['name' => 'Private Co', 'slug' => 'private-co', 'email' => 'p@example.com', 'is_active' => true]);
        TenantContext::set($tenant->id);

        return $tenant;
    }

    private function appointmentFor(Tenant $tenant): array
    {
        $customer = Customer::create(['tenant_id' => $tenant->id, 'name' => 'Pat', 'email' => 'pat@example.com', 'is_active' => true]);
        $appt = Appointment::create([
            'tenant_id' => $tenant->id, 'customer_id' => $customer->id,
            'scheduled_at' => now()->addDay(), 'duration_minutes' => 60, 'status' => 'pending',
        ]);

        return [$customer, $appt];
    }

    private function applicantDoc(Tenant $tenant, string $name, string $content): ApplicantDocument
    {
        $property = Property::create(['tenant_id' => $tenant->id, 'name' => 'Flat', 'address_line_1' => '1 Main', 'city' => 'Durban', 'is_active' => true]);
        $applicant = Applicant::create(['tenant_id' => $tenant->id, 'property_id' => $property->id, 'name' => 'Ayanda']);
        $path = "applicant-documents/{$name}";
        Storage::disk('local')->put($path, $content);

        return ApplicantDocument::create(['tenant_id' => $tenant->id, 'applicant_id' => $applicant->id, 'type' => 'id_copy', 'path' => $path, 'original_name' => $name]);
    }

    public function test_payment_proof_upload_lands_on_the_private_disk_and_serves_via_signed_link(): void
    {
        $tenant = $this->tenant();
        [$customer, $appt] = $this->appointmentFor($tenant);

        $this->actingAs($customer, 'customer')
            ->post(route('book.payment-proof', [$tenant->slug, $appt]), [
                'payment_proof' => UploadedFile::fake()->image('pop.jpg', 300, 300),
            ])->assertSessionHas('proof_uploaded');

        $appt->refresh();
        Storage::disk('local')->assertExists($appt->payment_proof_path);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $this->get($appt->paymentProofUrl())
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=1800, private')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_replacing_a_payment_proof_deletes_the_old_file(): void
    {
        $tenant = $this->tenant();
        [$customer, $appt] = $this->appointmentFor($tenant);
        $url = route('book.payment-proof', [$tenant->slug, $appt]);

        $this->actingAs($customer, 'customer')->post($url, ['payment_proof' => UploadedFile::fake()->image('a.jpg')]);
        $first = $appt->fresh()->payment_proof_path;
        $this->actingAs($customer, 'customer')->post($url, ['payment_proof' => UploadedFile::fake()->image('b.jpg')]);

        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($appt->fresh()->payment_proof_path);
    }

    public function test_unsigned_tampered_or_expired_links_are_refused(): void
    {
        $tenant = $this->tenant();
        $doc    = $this->applicantDoc($tenant, 'id.pdf', '%PDF-1.4 test');
        $other  = ApplicantDocument::create(['tenant_id' => $tenant->id, 'applicant_id' => $doc->applicant_id, 'type' => 'id_copy', 'path' => 'applicant-documents/other.pdf', 'original_name' => 'other.pdf']);

        $signed = $doc->url();
        $this->get($signed)->assertOk();

        $this->get("/files/applicant-doc/{$doc->id}")->assertForbidden();
        $this->get(str_replace("/applicant-doc/{$doc->id}", "/applicant-doc/{$other->id}", $signed))->assertForbidden();
        $this->get(str_replace('/applicant-doc/', '/payment-proof/', $signed))->assertForbidden();

        $this->travel(PrivateFile::LINK_MINUTES + PrivateFile::LINK_BUCKET_MINUTES + 1)->minutes();
        $this->get($signed)->assertForbidden();
    }

    public function test_only_safe_types_render_inline(): void
    {
        $tenant = $this->tenant();

        $pdf = $this->applicantDoc($tenant, 'payslip.pdf', '%PDF-1.4 test');
        $this->assertStringStartsWith('inline', $this->get($pdf->url())->headers->get('Content-Disposition'));

        $html = $this->applicantDoc($tenant, 'evil.html', '<script>alert(1)</script>');
        $this->assertStringStartsWith('attachment', $this->get($html->url())->headers->get('Content-Disposition'));
    }

    public function test_legacy_files_still_on_the_public_disk_keep_working_until_moved(): void
    {
        $tenant = $this->tenant();
        [, $appt] = $this->appointmentFor($tenant);
        Storage::disk('public')->put('payment_proofs/1/1/old.jpg', 'legacy');
        $appt->update(['payment_proof_path' => 'payment_proofs/1/1/old.jpg', 'payment_proof_name' => 'old.jpg']);

        $this->get($appt->paymentProofUrl())->assertOk();
    }

    public function test_privatize_command_moves_sensitive_folders_and_leaves_public_assets_alone(): void
    {
        foreach (['payment_proofs/1/2/p.jpg', 'applicant-documents/id.pdf', 'maintenance/leak.jpg', 'inspections/kitchen.jpg'] as $path) {
            Storage::disk('public')->put($path, 'x');
        }
        Storage::disk('public')->put('services/3/cover.jpg', 'marketing');
        Storage::disk('local')->put('maintenance/leak.jpg', 'x'); // already moved: must not be clobbered or duplicated

        $this->artisan('storage:privatize-uploads')->assertSuccessful();
        $this->artisan('storage:privatize-uploads')->assertSuccessful(); // idempotent

        $this->assertSame(['services/3/cover.jpg'], Storage::disk('public')->allFiles());
        foreach (['payment_proofs/1/2/p.jpg', 'applicant-documents/id.pdf', 'maintenance/leak.jpg', 'inspections/kitchen.jpg'] as $path) {
            Storage::disk('local')->assertExists($path);
        }
    }

    public function test_staff_appointment_page_links_the_proof_through_a_signed_url(): void
    {
        $this->seed(\Database\Seeders\PermissionRoleSeeder::class);
        $tenant = $this->tenant();
        $tenant->activateModule('booking');
        [, $appt] = $this->appointmentFor($tenant);
        Storage::disk('local')->put('payment_proofs/x/pop.jpg', 'img');
        $appt->update(['payment_proof_path' => 'payment_proofs/x/pop.jpg', 'payment_proof_name' => 'pop.jpg']);

        $staff = \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);
        $staff->assignRole('employee');
        TenantContext::clear();

        $this->actingAs($staff)->get(route('appointments.show', $appt))
            ->assertOk()
            ->assertSee('/files/payment-proof/' . $appt->id . '?expires=', false)
            ->assertDontSee('/storage/payment_proofs', false);
    }

    // ── Review fixes ────────────────────────────────────────────────────────

    public function test_a_file_that_fails_to_move_stays_public_and_the_command_fails(): void
    {
        Storage::disk('public')->put('applicant-documents/ok.pdf', 'ok');
        Storage::disk('public')->put('applicant-documents/sub/stuck.pdf', 'precious');
        // A file squatting where the target folder must go makes both rename and copy fail.
        Storage::disk('local')->put('applicant-documents/sub', 'not a folder');

        $this->artisan('storage:privatize-uploads')->assertFailed();

        Storage::disk('local')->assertExists('applicant-documents/ok.pdf');
        $this->assertSame('precious', Storage::disk('public')->get('applicant-documents/sub/stuck.pdf'), 'unmoved file must survive');
    }

    public function test_links_are_stable_within_the_cache_window(): void
    {
        $this->travelTo(now()->setTime(10, 5));
        $first = PrivateFile::url('applicant-doc', 1);
        $this->travelTo(now()->setTime(10, 25));

        $this->assertSame($first, PrivateFile::url('applicant-doc', 1));
    }

    public function test_an_expired_link_explains_itself(): void
    {
        $tenant = $this->tenant();
        $doc    = $this->applicantDoc($tenant, 'id.pdf', '%PDF-1.4 test');
        $url    = $doc->url();

        $this->travel(PrivateFile::LINK_MINUTES + PrivateFile::LINK_BUCKET_MINUTES + 1)->minutes();

        $this->get($url)->assertForbidden()->assertSee('This link has expired')->assertDontSee('Invalid signature');
    }

    public function test_unchanged_files_answer_with_304(): void
    {
        $tenant = $this->tenant();
        $doc    = $this->applicantDoc($tenant, 'id.pdf', '%PDF-1.4 test');

        $first = $this->get($doc->url())->assertOk();
        $this->get($doc->url(), ['If-Modified-Since' => $first->headers->get('Last-Modified')])->assertStatus(304);
    }

    public function test_file_requests_do_not_start_a_session(): void
    {
        $tenant = $this->tenant();
        $doc    = $this->applicantDoc($tenant, 'id.pdf', '%PDF-1.4 test');

        $response = $this->get($doc->url())->assertOk();
        $this->assertEmpty(array_filter($response->headers->getCookies(), fn ($c) => str_contains($c->getName(), 'session')));
    }

    public function test_a_proof_for_a_deleted_booking_is_not_served(): void
    {
        $tenant = $this->tenant();
        [, $appt] = $this->appointmentFor($tenant);
        Storage::disk('local')->put('payment_proofs/x/pop.jpg', 'img');
        $appt->update(['payment_proof_path' => 'payment_proofs/x/pop.jpg', 'payment_proof_name' => 'pop.jpg']);
        $url = $appt->paymentProofUrl();

        $appt->delete();

        $this->get($url)->assertNotFound();
    }

    public function test_staff_maintenance_photo_upload_is_private_and_renders_signed(): void
    {
        $this->seed(\Database\Seeders\PermissionRoleSeeder::class);
        $tenant = $this->tenant();
        $tenant->activateModule('property_management');
        $property = Property::create(['tenant_id' => $tenant->id, 'name' => 'Flat', 'address_line_1' => '1 Main', 'city' => 'Durban', 'is_active' => true]);
        $unit     = \App\Modules\Property\Models\Unit::create(['tenant_id' => $tenant->id, 'property_id' => $property->id, 'unit_number' => '1A', 'monthly_rent' => 5000]);
        $request  = \App\Modules\Property\Models\MaintenanceRequest::create([
            'tenant_id' => $tenant->id, 'property_id' => $property->id, 'unit_id' => $unit->id, 'title' => 'Leak', 'description' => 'Kitchen tap', 'priority' => 'low', 'status' => 'open',
        ]);
        $owner = \App\Models\User::factory()->create(['tenant_id' => $tenant->id]);
        $owner->assignRole('tenant-owner');
        TenantContext::clear();

        $this->actingAs($owner)
            ->post(route('maintenance.photos.store', $request), ['photos' => [UploadedFile::fake()->image('leak.jpg')]])
            ->assertSessionHas('success');

        $photo = $request->photos()->firstOrFail();
        Storage::disk('local')->assertExists($photo->path);
        $this->assertSame([], Storage::disk('public')->allFiles());

        $this->actingAs($owner)->get(route('maintenance.show', $request))
            ->assertOk()
            ->assertSee('/files/maintenance-photo/' . $photo->id . '?expires=', false);
    }
}

