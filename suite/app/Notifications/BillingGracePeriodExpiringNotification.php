<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Notifications\Messages\MailMessage;

class BillingGracePeriodExpiringNotification extends MailNotification
{

    public function __construct(public Tenant $tenant, public int $daysLeft) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("URGENT: Account suspension in {$this->daysLeft} day(s)")
            ->line("Your grace period expires in {$this->daysLeft} day(s). Pay now to avoid suspension.")
            ->action('Pay Now', url('/billing'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => "Suspension in {$this->daysLeft} day(s)",
            'message' => 'Pay your overdue invoice now to keep your account active.',
            'url'     => '/billing',
            'icon'    => 'bell',
        ];
    }
}
