<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class ClientPaymentConfirmedNotification extends MailNotification
{

    public function __construct(public float $amount, public string $reference) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Confirmed â€” R' . number_format($this->amount, 2))
            ->line("Your payment of R" . number_format($this->amount, 2) . " has been confirmed.")
            ->line("Reference: {$this->reference}")
            ->action('View Portal', url('/portal/dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Payment confirmed',
            'message' => 'R' . number_format($this->amount, 2) . " received. Ref: {$this->reference}",
            'url'     => '/portal/dashboard',
            'icon'    => 'payment',
        ];
    }
}
