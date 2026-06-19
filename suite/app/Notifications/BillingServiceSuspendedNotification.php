<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class BillingServiceSuspendedNotification extends MailNotification
{

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your account has been suspended')
            ->line('Your Xquisite Creation account has been suspended due to non-payment.')
            ->line('Please pay your outstanding invoice to reactivate your account.')
            ->action('View Billing', url('/billing'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Account suspended',
            'message' => 'Your account has been suspended. Pay your invoice to restore access.',
            'url'     => '/billing',
            'icon'    => 'bell',
        ];
    }
}
