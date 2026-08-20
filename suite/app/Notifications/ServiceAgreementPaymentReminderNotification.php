<?php

namespace App\Notifications;

use App\Modules\ServiceDelivery\Models\ServiceAgreement;
use App\Modules\ServiceDelivery\Models\ServiceAgreementCharge;
use Illuminate\Notifications\Messages\MailMessage;

class ServiceAgreementPaymentReminderNotification extends MailNotification
{
    public function __construct(
        public ServiceAgreement $agreement,
        public ServiceAgreementCharge $charge,
        public int $daysOverdue
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $client = $this->agreement->client;

        return (new MailMessage)
            ->subject("Payment reminder — {$client->name} is {$this->daysOverdue} days overdue")
            ->line("The {$this->agreement->plan_name} agreement for {$client->name} has an unpaid charge for {$this->charge->periodLabel()} (R" . number_format($this->charge->amount_due, 2) . ").")
            ->line("This is now {$this->daysOverdue} days overdue — the SLA policy calls for suspension at Day 10.")
            ->action('View Agreement', url('/service-agreements/' . $this->agreement->id));
    }

    public function toArray(object $notifiable): array
    {
        $client = $this->agreement->client;

        return [
            'title'   => "Payment overdue — {$client->name}",
            'message' => "{$this->agreement->plan_name} is {$this->daysOverdue} days overdue.",
            'url'     => '/service-agreements/' . $this->agreement->id,
            'icon'    => 'bell',
        ];
    }
}
