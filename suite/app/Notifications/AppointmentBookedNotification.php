<?php

namespace App\Notifications;

use App\Models\User;
use App\Modules\Booking\Models\Appointment;
use App\Notifications\Concerns\SendsWebPush;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class AppointmentBookedNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsWebPush;

    public function __construct(
        public Appointment $appointment,
        public User $booker,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->withWebPush(['database', 'broadcast'], $notifiable);
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

    // toDatabase() above has no 'title'/'url', so the trait's generic default
    // doesn't fit — build the push payload directly instead.
    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $data = $this->toDatabase($notifiable);

        return (new WebPushMessage)
            ->title('New booking')
            ->icon('/img/android-icon-192x192.png')
            ->body($data['message'])
            ->data(['url' => route('appointments.show', $this->appointment)]);
    }
}