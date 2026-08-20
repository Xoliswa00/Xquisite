<?php

namespace App\Notifications;

use App\Modules\ServiceDelivery\Models\ServiceAgreement;
use Illuminate\Notifications\Messages\MailMessage;

class ServiceAgreementSuspendedNotification extends MailNotification
{
    public function __construct(public ServiceAgreement $agreement) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $client = $this->agreement->client;

        return (new MailMessage)
            ->subject("Service suspended — {$client->name}")
            ->line("The {$this->agreement->plan_name} agreement for {$client->name} has been suspended after 10 days of non-payment.")
            ->line("A reactivation fee of R" . number_format($this->agreement->reactivation_fee, 2) . ' will apply once payment is received.')
            ->action('View Agreement', url('/service-agreements/' . $this->agreement->id));
    }

    public function toArray(object $notifiable): array
    {
        $client = $this->agreement->client;

        return [
            'title'   => "Suspended — {$client->name}",
            'message' => "{$this->agreement->plan_name} suspended after 10 days non-payment.",
            'url'     => '/service-agreements/' . $this->agreement->id,
            'icon'    => 'warning',
        ];
    }
}
