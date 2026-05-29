<?php

namespace App\Observers;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentReminder;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        $this->scheduleReminders($appointment);
    }

    public function updated(Appointment $appointment): void
    {
        // Re-schedule reminders whenever status or time changes
        if ($appointment->isDirty(['scheduled_at', 'status'])) {
            // Delete existing unsent reminders
            $appointment->reminders()->where('status', 'pending')->delete();

            if (in_array($appointment->status, ['pending', 'confirmed'])) {
                $this->scheduleReminders($appointment);
            }
        }
    }

    private function scheduleReminders(Appointment $appointment): void
    {
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return;
        }

        $customer = $appointment->customer;
        if (!$customer || !$customer->email) {
            return;
        }

        $remind24h = $appointment->scheduled_at->copy()->subHours(24);
        $remind1h  = $appointment->scheduled_at->copy()->subHour();

        // Only create reminders that are still in the future
        if ($remind24h->isFuture()) {
            AppointmentReminder::create([
                'tenant_id'      => $appointment->tenant_id,
                'appointment_id' => $appointment->id,
                'type'           => '24h',
                'scheduled_at'   => $remind24h,
                'status'         => 'pending',
            ]);
        }

        if ($remind1h->isFuture()) {
            AppointmentReminder::create([
                'tenant_id'      => $appointment->tenant_id,
                'appointment_id' => $appointment->id,
                'type'           => '1h',
                'scheduled_at'   => $remind1h,
                'status'         => 'pending',
            ]);
        }
    }
}
