<?php

namespace App\Modules\Booking\Observers;

use App\Mail\AppointmentReminderEmail;
use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\AppointmentReminder;
use Illuminate\Support\Facades\Mail;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        $this->scheduleReminders($appointment);
    }

    public function updated(Appointment $appointment): void
    {
        // Reschedule reminders when the appointment time changes
        if ($appointment->wasChanged('scheduled_at')) {
            $appointment->reminders()->where('status', 'pending')->delete();
            $this->scheduleReminders($appointment);
        }

        // Cancel pending reminders if the appointment is cancelled or no-show
        if ($appointment->wasChanged('status') && in_array($appointment->status, ['cancelled', 'no_show'])) {
            $appointment->reminders()->where('status', 'pending')->update(['status' => 'cancelled']);
        }
    }

    private function scheduleReminders(Appointment $appointment): void
    {
        $scheduledAt = $appointment->scheduled_at;

        // 24-hour reminder — skip if appointment is less than 24h away
        if ($scheduledAt->diffInHours(now(), false) < -1) {
            AppointmentReminder::create([
                'tenant_id'      => $appointment->tenant_id,
                'appointment_id' => $appointment->id,
                'type'           => 'email',
                'scheduled_at'   => $scheduledAt->copy()->subDay(),
                'status'         => 'pending',
            ]);
        }

        // 1-hour reminder — skip if appointment is less than 1h away
        if ($scheduledAt->diffInMinutes(now(), false) < -30) {
            AppointmentReminder::create([
                'tenant_id'      => $appointment->tenant_id,
                'appointment_id' => $appointment->id,
                'type'           => 'sms',
                'scheduled_at'   => $scheduledAt->copy()->subHour(),
                'status'         => 'pending',
            ]);
        }
    }
}
