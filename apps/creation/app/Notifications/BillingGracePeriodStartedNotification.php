<?php

namespace App\Notifications;

use App\Models\PlatformInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillingGracePeriodStartedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PlatformInvoice $invoice,
        public int $graceDays = 5
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ACTION REQUIRED: Payment Overdue — ' . $this->graceDays . '-Day Grace Period Started')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your invoice **' . $this->invoice->invoice_number . '** (R' . number_format($this->invoice->amount, 2) . ') is overdue.')
            ->line('We have started a **' . $this->graceDays . '-day grace period**. Your service will be suspended if payment is not received within this time.')
            ->action('Pay Now', url('/billing'))
            ->line('If you have already paid, please contact support with your payment reference.')
            ->salutation('The Creation Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'  => 'bell',
            'title' => 'Grace Period Started — Pay Within ' . $this->graceDays . ' Days',
            'body'  => 'Invoice ' . $this->invoice->invoice_number . ' (R' . number_format($this->invoice->amount, 2) . ') is overdue. Service suspends in ' . $this->graceDays . ' days.',
            'url'   => '/billing',
        ];
    }
}
