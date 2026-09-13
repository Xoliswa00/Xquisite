<?php

namespace Tests\Feature\Booking;

use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentReminder;
use App\Modules\Booking\Models\Customer;
use App\Modules\Booking\Models\Service;
use App\Services\Tenant\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression coverage for the dual-observer bug (module review, 2026-09):
 * App\Modules\Booking\Observers\AppointmentObserver (stale, deleted) and
 * App\Observers\AppointmentObserver (current) were both registered and both
 * wrote AppointmentReminder rows on every create/update, so a single
 * appointment ended up with up to 4 rows (types 24h/1h + email/sms) and
 * customers were emailed roughly twice per reminder. These tests assert the
 * exact row count and type set the single remaining observer should produce.
 */
class AppointmentReminderObserverTest extends TestCase
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
            'duration_minutes' => 60,
            'price'            => 250,
            'pricing_type'     => 'flat',
            'is_active'        => true,
        ]);
    }

    private function makeCustomer(Tenant $tenant, ?string $email = 'jane@example.com'): Customer
    {
        return Customer::create([
            'tenant_id' => $tenant->id,
            'name'      => 'Jane Client',
            'email'     => $email,
            'is_active' => true,
        ]);
    }

    private function bookAppointment(Tenant $tenant, Customer $customer, Service $service, Carbon $scheduledAt): Appointment
    {
        $appt = Appointment::create([
            'tenant_id'        => $tenant->id,
            'customer_id'      => $customer->id,
            'staff_id'         => null,
            'scheduled_at'     => $scheduledAt,
            'duration_minutes' => $service->duration_minutes,
            'status'           => 'confirmed',
        ]);
        $appt->services()->attach($service->id, [
            'duration_minutes' => $service->duration_minutes,
            'price_at_booking' => $service->price,
        ]);

        return $appt;
    }

    public function test_booking_two_days_out_schedules_exactly_one_24h_and_one_1h_reminder(): void
    {
        $tenant   = $this->makeTenant();
        $customer = $this->makeCustomer($tenant);
        $service  = $this->makeService($tenant);

        $appt = $this->bookAppointment($tenant, $customer, $service, Carbon::now()->addDays(2));

        $reminders = AppointmentReminder::where('appointment_id', $appt->id)->get();

        $this->assertCount(2, $reminders, 'exactly two reminders should exist — the dual-observer bug produced up to four');
        $this->assertEqualsCanonicalizing(['24h', '1h'], $reminders->pluck('type')->all());
        $this->assertTrue($reminders->every(fn ($r) => $r->status === 'pending'));
    }

    public function test_booking_twelve_hours_out_only_schedules_the_1h_reminder(): void
    {
        $tenant   = $this->makeTenant();
        $customer = $this->makeCustomer($tenant);
        $service  = $this->makeService($tenant);

        // The 24h-before mark for a booking 12h out is already in the past.
        $appt = $this->bookAppointment($tenant, $customer, $service, Carbon::now()->addHours(12));

        $reminders = AppointmentReminder::where('appointment_id', $appt->id)->get();

        $this->assertCount(1, $reminders);
        $this->assertSame('1h', $reminders->first()->type);
    }

    public function test_booking_thirty_minutes_out_schedules_no_reminders(): void
    {
        $tenant   = $this->makeTenant();
        $customer = $this->makeCustomer($tenant);
        $service  = $this->makeService($tenant);

        $appt = $this->bookAppointment($tenant, $customer, $service, Carbon::now()->addMinutes(30));

        $this->assertSame(0, AppointmentReminder::where('appointment_id', $appt->id)->count());
    }

    public function test_no_reminders_are_scheduled_for_a_customer_with_no_email(): void
    {
        $tenant   = $this->makeTenant();
        $customer = $this->makeCustomer($tenant, email: null);
        $service  = $this->makeService($tenant);

        $appt = $this->bookAppointment($tenant, $customer, $service, Carbon::now()->addDays(2));

        $this->assertSame(0, AppointmentReminder::where('appointment_id', $appt->id)->count());
    }

    public function test_rescheduling_replaces_pending_reminders_instead_of_adding_to_them(): void
    {
        $tenant   = $this->makeTenant();
        $customer = $this->makeCustomer($tenant);
        $service  = $this->makeService($tenant);

        $appt = $this->bookAppointment($tenant, $customer, $service, Carbon::now()->addDays(2));
        $this->assertCount(2, AppointmentReminder::where('appointment_id', $appt->id)->get());

        $appt->update(['scheduled_at' => Carbon::now()->addDays(3)]);

        $reminders = AppointmentReminder::where('appointment_id', $appt->id)->where('status', 'pending')->get();
        $this->assertCount(2, $reminders, 'rescheduling must not accumulate duplicate reminders');
        $this->assertTrue($reminders->every(
            fn ($r) => $r->scheduled_at->isSameDay($appt->scheduled_at->copy()->subDay())
                || $r->scheduled_at->equalTo($appt->scheduled_at->copy()->subHour())
        ));
    }

    public function test_cancelling_an_appointment_removes_its_pending_reminders(): void
    {
        $tenant   = $this->makeTenant();
        $customer = $this->makeCustomer($tenant);
        $service  = $this->makeService($tenant);

        $appt = $this->bookAppointment($tenant, $customer, $service, Carbon::now()->addDays(2));
        $this->assertCount(2, AppointmentReminder::where('appointment_id', $appt->id)->get());

        $appt->update(['status' => 'cancelled']);

        // The current observer hard-deletes pending reminders on cancel (rather
        // than flagging them cancelled) — no reminder rows survive.
        $this->assertSame(0, AppointmentReminder::where('appointment_id', $appt->id)->count());
    }
}
