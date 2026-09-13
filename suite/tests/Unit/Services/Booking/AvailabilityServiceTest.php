<?php

namespace Tests\Unit\Services\Booking;

use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Modules\Booking\Models\Staff;
use App\Modules\Booking\Models\StaffBlock;
use App\Modules\Booking\Models\StaffSchedule;
use App\Services\Booking\AvailabilityService;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The booking engine's core scheduling logic had zero test coverage before
 * this — everything here previously relied on the write-up in the module
 * review being right by inspection alone.
 */
class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $availability;

    protected function setUp(): void
    {
        parent::setUp();
        $this->availability = new AvailabilityService();
    }

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

    /** Staff member working Mon–Fri, 09:00–17:00, unless $days overrides it. */
    private function makeStaff(Tenant $tenant, array $days = [1, 2, 3, 4, 5], string $start = '09:00', string $end = '17:00'): Staff
    {
        $staff = Staff::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Thandi Stylist',
            'is_active' => true,
        ]);

        foreach ($days as $day) {
            StaffSchedule::create([
                'tenant_id'   => $tenant->id,
                'staff_id'    => $staff->id,
                'day_of_week' => $day,
                'start_time'  => $start,
                'end_time'    => $end,
                'is_active'   => true,
            ]);
        }

        return $staff;
    }

    private function makeService(Tenant $tenant, int $duration = 60): Service
    {
        return Service::create([
            'tenant_id'        => $tenant->id,
            'name'             => 'Haircut',
            'duration_minutes' => $duration,
            'price'            => 250,
            'pricing_type'     => 'flat',
            'is_active'        => true,
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

    /** A confirmed appointment for $staff at $start, $duration minutes long. */
    private function bookAppointment(Tenant $tenant, Staff $staff, Service $service, Customer $customer, Carbon $start, int $duration, string $status = 'confirmed'): Appointment
    {
        $appt = Appointment::create([
            'tenant_id'        => $tenant->id,
            'customer_id'      => $customer->id,
            'staff_id'         => $staff->id,
            'scheduled_at'     => $start,
            'duration_minutes' => $duration,
            'status'           => $status,
        ]);
        $appt->services()->attach($service->id, [
            'duration_minutes' => $duration,
            'price_at_booking' => $service->price,
        ]);

        return $appt;
    }

    /** The next occurrence of a given ISO day-of-week (1=Mon..7=Sun), at 10:00. */
    private function nextDay(int $isoWeekday): Carbon
    {
        return Carbon::now()->next($isoWeekday % 7)->setTime(10, 0);
    }

    public function test_hard_stop_blocks_a_day_the_staff_member_does_not_work(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant, days: [1, 2, 3, 4, 5]); // Mon–Fri only

        $sunday = $this->nextDay(7);

        $reason = $this->availability->checkHardStop($staff, $sunday, 60);

        $this->assertNotNull($reason);
        $this->assertStringContainsString('does not work on Sundays', $reason);
    }

    public function test_hard_stop_blocks_a_time_outside_working_hours(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant, days: [1], start: '09:00', end: '17:00');

        $tooEarly = $this->nextDay(1)->setTime(8, 0);

        $reason = $this->availability->checkHardStop($staff, $tooEarly, 60);

        $this->assertNotNull($reason);
        $this->assertStringContainsString('09:00', $reason);
    }

    public function test_hard_stop_blocks_a_time_that_would_run_past_closing(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant, days: [1], start: '09:00', end: '17:00');

        // Starts inside hours but a 90-minute job would end at 16:30 — fine —
        // vs. one starting at 16:00 for 90 minutes, which runs to 17:30.
        $tooLate = $this->nextDay(1)->setTime(16, 0);

        $reason = $this->availability->checkHardStop($staff, $tooLate, 90);

        $this->assertNotNull($reason);
    }

    public function test_hard_stop_blocks_a_time_inside_a_staff_block(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant, days: [1]);
        $monday = $this->nextDay(1);

        StaffBlock::create([
            'tenant_id' => $tenant->id,
            'staff_id'  => $staff->id,
            'starts_at' => $monday->copy()->setTime(9, 30),
            'ends_at'   => $monday->copy()->setTime(11, 0),
            'reason'    => 'Dentist appointment',
        ]);

        $reason = $this->availability->checkHardStop($staff, $monday->copy()->setTime(10, 0), 30);

        $this->assertNotNull($reason);
        $this->assertStringContainsString('Dentist appointment', $reason);
    }

    public function test_hard_stop_is_clear_when_nothing_conflicts(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant, days: [1]);

        $reason = $this->availability->checkHardStop($staff, $this->nextDay(1)->setTime(10, 0), 60);

        $this->assertNull($reason);
    }

    public function test_get_conflicts_detects_overlap_and_ignores_cancelled_appointments(): void
    {
        $tenant   = $this->makeTenant();
        $staff    = $this->makeStaff($tenant, days: [1]);
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $monday   = $this->nextDay(1);

        $existing = $this->bookAppointment($tenant, $staff, $service, $customer, $monday->copy()->setTime(10, 0), 30);

        // A new 10:15–10:45 request overlaps the existing 10:00–10:30 booking.
        $conflicts = $this->availability->getConflicts($staff, $monday->copy()->setTime(10, 15), 30);
        $this->assertCount(1, $conflicts);

        $existing->update(['status' => 'cancelled']);

        $conflicts = $this->availability->getConflicts($staff, $monday->copy()->setTime(10, 15), 30);
        $this->assertCount(0, $conflicts, 'a cancelled appointment must not count as a conflict');
    }

    public function test_get_conflicts_excludes_the_given_appointment_id(): void
    {
        $tenant   = $this->makeTenant();
        $staff    = $this->makeStaff($tenant, days: [1]);
        $service  = $this->makeService($tenant);
        $customer = $this->makeCustomer($tenant);
        $monday   = $this->nextDay(1);

        $appt = $this->bookAppointment($tenant, $staff, $service, $customer, $monday->copy()->setTime(10, 0), 30);

        // Checking the same appointment's own slot against itself should not
        // self-conflict when its own id is excluded (used when editing/reassigning).
        $conflicts = $this->availability->getConflicts($staff, $monday->copy()->setTime(10, 0), 30, excludeId: $appt->id);

        $this->assertCount(0, $conflicts);
    }

    public function test_available_slots_generates_a_grid_within_working_hours(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant, days: [1], start: '09:00', end: '11:00');

        $slots = $this->availability->availableSlots($staff, $this->nextDay(1), 60);

        // 09:00–11:00 with a 60-minute job: last possible start is 10:00.
        $this->assertSame('09:00', $slots->first()->format('H:i'));
        $this->assertSame('10:00', $slots->last()->format('H:i'));
    }

    public function test_available_slots_excludes_a_blocked_window(): void
    {
        $tenant = $this->makeTenant();
        $staff  = $this->makeStaff($tenant, days: [1], start: '09:00', end: '12:00');
        $monday = $this->nextDay(1);

        StaffBlock::create([
            'tenant_id' => $tenant->id,
            'staff_id'  => $staff->id,
            'starts_at' => $monday->copy()->setTime(10, 0),
            'ends_at'   => $monday->copy()->setTime(11, 0),
            'reason'    => 'Break',
        ]);

        $slots = $this->availability->availableSlots($staff, $monday, 30);

        $blockedStarts = $slots->filter(fn ($s) => $s->format('H:i') >= '10:00' && $s->format('H:i') < '11:00');
        $this->assertCount(0, $blockedStarts, 'no slot should start inside the blocked window');
        $this->assertTrue($slots->contains(fn ($s) => $s->format('H:i') === '09:00'));
        $this->assertTrue($slots->contains(fn ($s) => $s->format('H:i') === '11:00'));
    }

    public function test_available_slots_for_duration_only_offers_staff_qualified_for_the_service(): void
    {
        $tenant   = $this->makeTenant();
        $service  = $this->makeService($tenant);
        $qualified   = $this->makeStaff($tenant, days: [1]);
        $unqualified = $this->makeStaff($tenant, days: [1]);
        $qualified->services()->attach($service->id);
        // $unqualified is deliberately NOT linked to the service.

        $slots = $this->availability->availableSlotsForDuration(60, $this->nextDay(1), [$service->id]);

        $this->assertGreaterThan(0, $slots->count());

        // Book out the qualified staff member's entire day — the unqualified
        // one being free must NOT fill the gap, since they can't perform this service.
        $customer = $this->makeCustomer($tenant);
        $monday   = $this->nextDay(1);
        foreach ($this->availability->availableSlots($qualified, $monday, 60) as $slot) {
            $this->bookAppointment($tenant, $qualified, $service, $customer, $slot, 60);
        }

        $slotsAfter = $this->availability->availableSlotsForDuration(60, $monday, [$service->id]);
        $this->assertCount(0, $slotsAfter, 'an unqualified but free staff member must not be offered for this service');
    }
}
