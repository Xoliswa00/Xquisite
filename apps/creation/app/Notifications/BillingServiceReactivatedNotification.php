<?php

namespace App\Notifications;

use App\Models\PlatformInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillingServiceReactivatedNotification extends Notification
{
    use Queueable;

    public function __construct(public PlatformInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Service Reactivated — Payment Received')
            ->greeting('Welcome back, ' . $notifiable->name . '!')
            ->line('Payment for invoice **' . $this->invoice->invoice_number . '** (R' . number_format($this->invoice->amount, 2) . ') has been received.')
            ->line('Your service is now **fully active** again. All features and client access have been restored.')
            ->action('Go to Dashboard', url('/dashboard'))
            ->salutation('The Creation Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'  => 'payment',
            'title' => 'Service Reactivated',
            'body'  => 'Payment confirmed for invoice ' . $this->invoice->invoice_number . '. Your service is back online.',
            'url'   => '/billing',
        ];
    }
}
