<?php

namespace App\Notifications;

use App\Modules\Booking\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public float $amountDue,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
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
}
