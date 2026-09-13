<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\StaffSchedule;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Regression coverage for the cross-customer slot race (module review,
 * 2026-09): PublicBookingController::store() used to lock on
 * 'booking-submit:{customer_id}', so two DIFFERENT customers targeting the
 * exact same start time could both pass the availability check before either
 * insert landed. The lock is now keyed on tenant+slot instead.
 */
class BookingSlotConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    private function makeTenant(): Tenant
    {
        $tenant = Tenant::create([
            'name'      => 'Test Salon',
            'slug'      => 'test-salon-' . uniqid(),
            'email'     => 'salon@example.com',
            'is_active' => true,
        ]);
        TenantContext::set($tenant->id);

        return $tenant;
    }

    private function makeService(Tenant $tenant): Service
    {
        return Service::create([
            'tenant_id'        => $tenant->id,
            'name'             => 'Haircut',
            'duration_minutes' => 30,
            'price'            => 150,
            'pricing_type'     => 'flat',
            'is_active'        => true,
        ]);
    }

    /** A single staff member who can only cover one customer per slot. */
    private function makeSoleStaff(Tenant $tenant, Service $service, Carbon $slot): Staff
    {
        $staff = Staff::create(['tenant_id' => $tenant->id, 'name' => 'Solo Stylist', 'is_active' => true]);
        $staff->services()->attach($service->id);

        StaffSchedule::create([
            'tenant_id'   => $tenant->id,
            'staff_id'    => $staff->id,
            'day_of_week' => $slot->dayOfWeek,
            'start_time'  => '00:00',
            'end_time'    => '23:45',
            'is_active'   => true,
        ]);

        return $staff;
    }

    private function makeCustomer(Tenant $tenant, string $email): Customer
    {
        return Customer::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Client ' . $email,
            'email'     => $email,
            'is_active' => true,
        ]);
    }

    private function submit(Tenant $tenant, Customer $customer, Service $service, Carbon $slot)
    {
        $this->actingAs($customer, 'customer');
        session(['pending_booking' => [
            'service_ids'  => [$service->id],
            'scheduled_at' => $slot->format('Y-m-d H:i'),
            'combo_id'     => null,
            'quantities'   => [],
        ]]);

        return $this->post(route('book.store', $tenant->slug));
    }

    public function test_a_second_customer_cannot_take_a_slot_the_first_already_booked(): void
    {
        $tenant  = $this->makeTenant();
        $service = $this->makeService($tenant);
        $slot    = Carbon::now()->addDay()->setTime(10, 0);
        $this->makeSoleStaff($tenant, $service, $slot);

        $customerA = $this->makeCustomer($tenant, 'a@example.com');
        $customerB = $this->makeCustomer($tenant, 'b@example.com');

        $this->submit($tenant, $customerA, $service, $slot)->assertRedirect();
        $this->assertSame(1, Appointment::count());

        // Same tenant, same exact start time, the only qualified staff member
        // is already fully occupied for this slot.
        $response = $this->submit($tenant, $customerB, $service, $slot);

        $response->assertSessionHasErrors('slot');
        $this->assertSame(1, Appointment::count(), 'the second customer must not have booked the same slot');
    }

    public function test_a_submission_is_serialized_against_a_concurrent_hold_on_the_same_tenant_and_slot(): void
    {
        $tenant  = $this->makeTenant();
        $service = $this->makeService($tenant);
        $slot    = Carbon::now()->addDay()->setTime(14, 0);
        $this->makeSoleStaff($tenant, $service, $slot);
        $customer = $this->makeCustomer($tenant, 'lockholder@example.com');

        // Simulate a concurrent request already holding the tenant+slot lock —
        // this is the exact key PublicBookingController::store() now uses.
        $lockKey = 'booking-slot:' . $tenant->id . ':' . $slot->format('YmdHi');
        $held    = Cache::lock($lockKey, 15);
        $this->assertTrue($held->get(), 'test setup: could not acquire the contended lock');

        try {
            $response = $this->submit($tenant, $customer, $service, $slot);

            // store()'s ->block(5, ...) waits, then hits LockTimeoutException
            // and redirects with a friendly "still processing" message instead
            // of creating a second appointment underneath the held lock.
            $response->assertRedirect(route('book.my-bookings', $tenant->slug));
            $this->assertSame(0, Appointment::count());
        } finally {
            $held->release();
        }
    }

    public function test_two_different_slots_do_not_contend_for_the_same_lock(): void
    {
        $tenant  = $this->makeTenant();
        $service = $this->makeService($tenant);
        $slotA   = Carbon::now()->addDay()->setTime(9, 0);
        $slotB   = Carbon::now()->addDay()->setTime(15, 0);
        $this->makeSoleStaff($tenant, $service, $slotA);

        $customerA = $this->makeCustomer($tenant, 'a2@example.com');
        $customerB = $this->makeCustomer($tenant, 'b2@example.com');

        $this->submit($tenant, $customerA, $service, $slotA)->assertRedirect();
        $this->submit($tenant, $customerB, $service, $slotB)->assertRedirect();

        $this->assertSame(2, Appointment::count(), 'unrelated slots must not block each other');
    }
}
