<?php

namespace App\Notifications;

use App\Models\PlatformInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillingGracePeriodExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PlatformInvoice $invoice,
        public int $daysLeft
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urgency = $this->daysLeft === 1 ? '⚠️ URGENT: ' : '';

        return (new MailMessage)
            ->subject($urgency . 'Service Suspends in ' . $this->daysLeft . ' ' . ($this->daysLeft === 1 ? 'Day' : 'Days'))
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your grace period expires in **' . $this->daysLeft . ' ' . ($this->daysLeft === 1 ? 'day' : 'days') . '**.')
            ->line('Invoice **' . $this->invoice->invoice_number . '** for R' . number_format($this->invoice->amount, 2) . ' remains unpaid.')
            ->line('Your service will be automatically **suspended** if payment is not received.')
            ->action('Pay Now to Avoid Suspension', url('/billing'))
            ->salutation('The Creation Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'  => 'bell',
            'title' => 'Suspension in ' . $this->daysLeft . ' ' . ($this->daysLeft === 1 ? 'Day' : 'Days') . '!',
            'body'  => 'Invoice ' . $this->invoice->invoice_number . ' still unpaid. Pay now to keep your service active.',
            'url'   => '/billing',
        ];
    }
}
