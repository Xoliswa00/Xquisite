<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Service;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTermsAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(array $overrides = []): Tenant
    {
        return Tenant::create(array_merge([
            'name'      => 'Test Salon',
            'slug'      => 'test-salon-' . uniqid(),
            'email'     => 'salon@example.com',
            'is_active' => true,
        ], $overrides));
    }

    private function makeService(Tenant $tenant): Service
    {
        TenantContext::set($tenant->id);

        // duration_minutes >= 1440 makes this a multi-day booking, which
        // skips the slot-availability check entirely — keeps the test
        // focused on the terms-acceptance guard, not staff scheduling.
        return Service::create([
            'tenant_id'         => $tenant->id,
            'name'              => 'Multi-day Retreat',
            'duration_minutes'  => 1440,
            'price'             => 500,
            'pricing_type'      => 'flat',
            'is_active'         => true,
        ]);
    }

    private function makeCustomer(Tenant $tenant): Customer
    {
        return Customer::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Jane Client',
            'email'     => 'jane@example.com',
            'is_active' => true,
        ]);
    }

    private function submitBooking(Tenant $tenant, Customer $customer, Service $service, array $extra = [])
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

    public function test_booking_succeeds_without_terms_when_policy_not_required(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);

        $response = $this->submitBooking($tenant, $customer, $service);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors('accepted_terms');
        $this->assertSame(1, Appointment::count());
        $this->assertNull(Appointment::first()->terms_accepted_at);
    }

    public function test_booking_rejected_without_accepting_required_terms(): void
    {
        $tenant   = $this->makeTenant([
            'booking_terms' => 'No refunds within 24 hours.',
            'require_booking_terms_acceptance' => true,
        ]);
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);

        $response = $this->submitBooking($tenant, $customer, $service);

        $response->assertSessionHasErrors('accepted_terms');
        $this->assertSame(0, Appointment::count());
    }

    public function test_booking_succeeds_and_stamps_acceptance_when_terms_are_accepted(): void
    {
        $tenant   = $this->makeTenant([
            'booking_terms' => 'No refunds within 24 hours.',
            'require_booking_terms_acceptance' => true,
        ]);
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);

        $response = $this->submitBooking($tenant, $customer, $service, ['accepted_terms' => '1']);

        $response->assertRedirect();
        $response->assertSessionDoesntHaveErrors();
        $appointment = Appointment::first();
        $this->assertNotNull($appointment->terms_accepted_at);
    }
}
