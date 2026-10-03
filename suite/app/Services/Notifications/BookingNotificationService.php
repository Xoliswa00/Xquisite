<?php

namespace App\Services\Notifications;

use App\Models\Tenant;
use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Notifications\AppNotice;
use Illuminate\Support\Facades\Cache;

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

    /** How long repeat "new inspiration photos" notices for one booking are collapsed. */
    public const INSPIRATION_NOTICE_COOLDOWN_MINUTES = 10;

    /**
     * A customer added inspiration photos to an existing booking from My Bookings,
     * so the look is seen before the client walks in, not discovered at the chair.
     *
     * - Goes to owners/managers plus the assigned stylist (matched by email),
     *   not every employee, so the barber doesn't get the braider's pushes.
     * - One notice per booking per cooldown: adding photos one at a time (or
     *   delete/re-add) doesn't fire a push each time.
     * - Sent after the response, so web push round-trips never hold up the
     *   customer's upload.
     */
    public function notifyInspirationAdded(Appointment $appointment, int $count): void
    {
        $key = "inspiration-notice:{$appointment->id}";
        if (! Cache::add($key, true, now()->addMinutes(self::INSPIRATION_NOTICE_COOLDOWN_MINUTES))) {
            return;
        }

        dispatch(function () use ($appointment, $count) {
            $appointment->loadMissing(['customer', 'services', 'staff']);

            $who    = $appointment->customer?->name ?? 'A client';
            $photos = $count === 1 ? 'an inspiration photo' : "{$count} inspiration photos";

            $notice = new AppNotice(
                title: $count === 1 ? 'New inspiration photo' : 'New inspiration photos',
                message: "{$who} added {$photos} for {$this->servicesSummary($appointment)} on {$appointment->scheduled_at->format('d M Y, H:i')}.",
                url: route('appointments.show', $appointment),
                level: 'info'
            );

            foreach ($this->lookRecipients($appointment) as $user) {
                $user->notify($notice);
            }
        })->afterResponse();
    }

    /** Tell the client their look was saved, and that they can remove it (POPIA notice). */
    public function notifyLookSaved(Appointment $appointment): void
    {
        $appointment->loadMissing(['customer', 'services']);
        $tenant = Tenant::find($appointment->tenant_id);

        $this->notifyCustomer(
            $appointment,
            'Your look was saved',
            ($tenant?->name ?? 'Your salon') . " saved your look from {$appointment->scheduled_at->format('d M Y')} so you can book it again. "
                . 'You can remove it any time from My Bookings.',
            $this->customerPortalUrl($appointment)
        );
    }

    /**
     * Owners and managers, plus the assigned staff member when they have a login
     * with the same email. Falls back to all booking staff when nobody is assigned.
     */
    protected function lookRecipients(Appointment $appointment)
    {
        $tenantId = $this->resolveTenantId($appointment);
        if (! $tenantId) {
            return collect();
        }

        $base = User::query()->where('tenant_id', $tenantId)->where('is_active', true);

        if ($appointment->isUnassigned()) {
            return $base->whereHas('roles', fn ($q) => $q->whereIn('name', ['tenant-owner', 'manager', 'employee']))->get();
        }

        $staffEmail = $appointment->staff?->email;

        return $base->where(function ($q) use ($staffEmail) {
            $q->whereHas('roles', fn ($r) => $r->whereIn('name', ['tenant-owner', 'manager']));
            if ($staffEmail) {
                $q->orWhere('email', $staffEmail);
            }
        })->get();
    }

    public function notifyStaffScheduleChanged($staff, string $message): void
    {
        $users = User::query()
            ->where('tenant_id', $staff->tenant_id)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['tenant-owner', 'manager', 'employee']))
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
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['tenant-owner', 'manager', 'employee']))
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