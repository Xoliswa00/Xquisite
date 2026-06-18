<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class PaymentReceivedNotification extends MailNotification
{

    public function __construct(public float $amount, public string $reference, public string $method = '') {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Received â€” R' . number_format($this->amount, 2))
            ->line("A payment of R" . number_format($this->amount, 2) . " was recorded.")
            ->line("Reference: {$this->reference}")
            ->action('View Payment Plans', url('/payment-plans'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Payment received',
            'message' => 'R' . number_format($this->amount, 2) . " received. Ref: {$this->reference}",
            'url'     => '/payment-plans',
            'icon'    => 'payment',
        ];
    }
}
