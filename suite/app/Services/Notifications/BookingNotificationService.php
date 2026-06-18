<?php

namespace App\Services\Notifications;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Notifications\AppNotice;

class BookingNotificationService
{
    public function notifyAppointmentCreated(Appointment $appointment, ?string $customerUrl = null): void
    {
        $appointment->loadMissing(['customer', 'staff', 'services']);

        $servicesList = $this->servicesSummary($appointment);

        $this->notifyTenantStaff(
            $appointment,
            'New booking received',
            "A new booking for {$servicesList} is scheduled for {$appointment->scheduled_at->format('M j, Y H:i')}.",
            route('appointments.show', $appointment)
        );

        if ($appointment->customer) {
            $this->notifyCustomer(
                $appointment,
                'Booking confirmed',
                "Your appointment for {$servicesList} on {$appointment->scheduled_at->format('M j, Y H:i')} is confirmed and pending staff assignment.",
                $customerUrl ?? $this->customerPortalUrl($appointment)
            );
        }
    }

    public function notifyAppointmentUpdated(Appointment $appointment, array $changes = []): void
    {
        $appointment->loadMissing(['customer', 'staff', 'services']);

        $message = empty($changes)
            ? 'Appointment details were updated.'
            : 'Appointment updated: ' . implode(', ', $changes) . '.';

        $url         = route('appointments.show', $appointment);
        $customerUrl = $this->customerPortalUrl($appointment);

        $this->notifyTenantStaff($appointment, 'Booking updated', $message, $url);

        if ($appointment->customer) {
            $this->notifyCustomer($appointment, 'Booking updated', $message, $customerUrl);
        }
    }

    public function notifyAppointmentAssigned(Appointment $appointment): void
    {
        $appointment->loadMissing(['customer', 'staff', 'services']);

        if (!$appointment->staff) {
            return;
        }

        $servicesList = $this->servicesSummary($appointment);
        $message      = "{$appointment->staff->name} was assigned to the appointment on {$appointment->scheduled_at->format('M j, Y H:i')} for {$servicesList}.";
        $url          = route('appointments.show', $appointment);
        $customerUrl  = $this->customerPortalUrl($appointment);

        $this->notifyTenantStaff($appointment, 'Staff assigned', $message, $url);
        $this->notifyCustomer($appointment, 'Staff assigned', $message, $customerUrl);
    }

    public function notifyAppointmentCancelled(Appointment $appointment): void
    {
        $appointment->loadMissing(['customer', 'staff', 'services']);

        $servicesList = $this->servicesSummary($appointment);
        $message      = "The appointment for {$servicesList} on {$appointment->scheduled_at->format('M j, Y H:i')} was cancelled.";
        $url          = route('appointments.show', $appointment);
        $customerUrl  = $this->customerPortalUrl($appointment);

        $this->notifyTenantStaff($appointment, 'Booking cancelled', $message, $url);
        $this->notifyCustomer($appointment, 'Booking cancelled', $message, $customerUrl);
    }

    public function notifyStaffScheduleChanged($staff, string $message): void
    {
        $users = User::query()
            ->where('tenant_id', $staff->tenant_id)
            ->where('is_active', true)
            ->whereIn('role', ['owner', 'admin', 'staff'])
            ->get();

        foreach ($users as $user) {
            $user->notify(new AppNotice(
                title: 'Staff schedule updated',
                message: $message,
                url: route('staff.show', $staff),
                level: 'info'
            ));
        }
    }

    /**
     * Produce a readable comma-separated list of service names.
     * e.g. "Haircut, Colour & Blow-dry" or just "Haircut"
     */
    protected function servicesSummary(Appointment $appointment): string
    {
        $names = $appointment->services->pluck('name');

        if ($names->isEmpty()) {
            return 'appointment';
        }

        if ($names->count() === 1) {
            return $names->first();
        }

        $last = $names->pop();

        return $names->implode(', ') . ' & ' . $last;
    }

    protected function notifyTenantStaff(Appointment $appointment, string $title, string $message, ?string $url = null): void
    {
        $tenantId = $this->resolveTenantId($appointment);
        if (!$tenantId) {
            return;
        }

        $users = User::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereIn('role', ['owner', 'admin', 'staff'])
            ->get();

        foreach ($users as $user) {
            $user->notify(new AppNotice(
                title: $title,
                message: $message,
                url: $url,
                level: 'info'
            ));
        }
    }

    protected function notifyCustomer(Appointment $appointment, string $title, string $message, ?string $url = null): void
    {
        if (!$appointment->customer) {
            return;
        }

        $appointment->customer->notify(new AppNotice(
            title: $title,
            message: $message,
            url: $url ?? $this->customerPortalUrl($appointment),
            level: 'success'
        ));
    }

    protected function resolveTenantId(Appointment $appointment): ?int
    {
        return $appointment->tenant_id
            ?? $appointment->customer?->tenant_id
            ?? $appointment->staff?->tenant_id
            ?? null;
    }

    protected function customerPortalUrl(Appointment $appointment): ?string
    {
        $tenant = Tenant::find($appointment->tenant_id);

        return $tenant ? route('book.my-bookings', $tenant->slug) : null;
    }
}