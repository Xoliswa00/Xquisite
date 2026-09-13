<?php

namespace App\Notifications;

use App\Modules\Booking\Models\Appointment;
use App\Notifications\Concerns\SendsWebPush;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable, SendsWebPush;

    public function __construct(
        public Appointment $appointment,
        public float $amountDue,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->withWebPush(['mail', 'database'], $notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $date     = $this->appointment->scheduled_at->format('l, d F Y \a\t H:i');
        $services = $this->appointment->services->pluck('name')->join(', ');
        $amount   = 'R' . number_format($this->amountDue, 2);

        return (new MailMessage)
            ->subject('Payment reminder — ' . $date)
            ->greeting('Hi ' . $this->appointment->customer->name . ',')
            ->line("Thank you for visiting us on **{$date}** for **{$services}**.")
            ->line("We wanted to kindly remind you that an outstanding balance of **{$amount}** is still due.")
            ->line('Please contact us at your earliest convenience to arrange payment.')
            ->salutation('Thank you — ' . $this->appointment->staff?->name ?? 'The Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'message'        => 'Payment reminder sent to ' . $this->appointment->customer->name
                . ' — R' . number_format($this->amountDue, 2) . ' outstanding.',
            'type'           => 'payment_reminder',
        ];
    }

    // toDatabase() above has no 'title'/'url', so the trait's generic default
    // doesn't fit — build the push payload directly instead. This notification
    // goes to both the customer and the staff member who sent the reminder
    // (AppointmentController::remind), and each needs a different link — the
    // admin appointment page isn't reachable from the customer guard.
    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $url = $notifiable instanceof \App\Modules\Booking\Models\Customer
            ? route('book.my-bookings', $this->appointment->tenant->slug)
            : route('appointments.show', $this->appointment);

        return (new WebPushMessage)
            ->title('Payment reminder')
            ->icon('/img/android-icon-192x192.png')
            ->body('R' . number_format($this->amountDue, 2) . ' outstanding for your ' . $this->appointment->scheduled_at->format('d M') . ' appointment.')
            ->data(['url' => $url]);
    }
}
