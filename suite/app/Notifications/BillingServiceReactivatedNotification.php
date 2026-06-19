<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class BillingServiceReactivatedNotification extends MailNotification
{

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your account has been reactivated')
            ->line('Your Xquisite Creation account is now active again. Thank you for your payment!')
            ->action('Go to Dashboard', url('/dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Account reactivated',
            'message' => 'Your account is active again. Welcome back!',
            'url'     => '/dashboard',
            'icon'    => 'bell',
        ];
    }
}
