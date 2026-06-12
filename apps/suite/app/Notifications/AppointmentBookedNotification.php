<?php

namespace App\Notifications;

use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AppointmentBookedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public User $booker,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        $isBooker = $notifiable->id === $this->booker->id;

        return [
            'appointment_id' => $this->appointment->id,
            'message'        => $isBooker
                ? "You booked an appointment for {$this->appointment->customer->full_name}"
                : "A new appointment has been booked for you",
            'scheduled_at'   => $this->appointment->scheduled_at,
            'services'       => $this->appointment->services->pluck('name'),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}