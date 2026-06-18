<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Notifications\Messages\MailMessage;

class BillingGracePeriodStartedNotification extends MailNotification
{

    public function __construct(public Tenant $tenant) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $days = $this->tenant->graceDaysLeft();
        return (new MailMessage)
            ->subject('Grace Period Started â€” Action Required')
            ->line("Your platform invoice is overdue. You have a {$days}-day grace period to make payment before your account is suspended.")
            ->action('Pay Now', url('/billing'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Grace period started',
            'message' => "You have {$this->tenant->graceDaysLeft()} days to pay before suspension.",
            'url'     => '/billing',
            'icon'    => 'bell',
        ];
    }
}
