<?php

namespace App\Notifications;

use App\Models\PlatformInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillingServiceSuspendedNotification extends Notification
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
            ->subject('Your Service Has Been Suspended')
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your account has been **suspended** due to non-payment of invoice **' . $this->invoice->invoice_number . '** (R' . number_format($this->invoice->amount, 2) . ').')
            ->line('Your clients are unable to access the portal while your account is suspended.')
            ->action('Pay Now to Reactivate', url('/billing'))
            ->line('Once payment is confirmed, your service will be reactivated immediately.')
            ->salutation('The Creation Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'  => 'bell',
            'title' => 'Service Suspended',
            'body'  => 'Your account has been suspended due to unpaid invoice ' . $this->invoice->invoice_number . '. Pay to reactivate.',
            'url'   => '/billing',
        ];
    }
}
