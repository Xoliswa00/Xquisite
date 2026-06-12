<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $invoiceNumber,
        public float  $amount,
        public string $clientName,
        public int    $invoiceId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Payment received — {$this->invoiceNumber}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A payment of **R" . number_format($this->amount, 2) . "** was recorded against invoice **{$this->invoiceNumber}** for {$this->clientName}.")
            ->action('View Invoice', url("/invoices/{$this->invoiceId}"))
            ->line('Thank you for using the system.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Payment received — ' . $this->invoiceNumber,
            'body'  => "R" . number_format($this->amount, 2) . " received from {$this->clientName}.",
            'url'   => "/invoices/{$this->invoiceId}",
            'icon'  => 'payment',
        ];
    }
}
