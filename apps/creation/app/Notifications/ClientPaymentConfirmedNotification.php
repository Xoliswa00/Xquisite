<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ClientPaymentConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $invoiceNumber,
        public string $companyName,
        public float  $amount,
        public int    $invoiceId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment confirmed — {$this->invoiceNumber}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your payment of **R" . number_format($this->amount, 2) . "** against invoice **{$this->invoiceNumber}** has been recorded by {$this->companyName}.")
            ->action('View Receipt', url("/invoices/{$this->invoiceId}"))
            ->line('Thank you for your payment.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment confirmed',
            'body'  => "R" . number_format($this->amount, 2) . " on {$this->invoiceNumber} has been recorded.",
            'url'   => "/invoices/{$this->invoiceId}",
            'icon'  => 'payment',
        ];
    }
}
