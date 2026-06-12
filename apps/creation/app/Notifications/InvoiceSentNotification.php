<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvoiceSentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $invoiceNumber,
        public string $clientName,
        public float  $total,
        public int    $invoiceId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoice {$this->invoiceNumber} sent to {$this->clientName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Invoice **{$this->invoiceNumber}** for **R" . number_format($this->total, 2) . "** has been sent to {$this->clientName}.")
            ->action('View Invoice', url("/invoices/{$this->invoiceId}"));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Invoice {$this->invoiceNumber} sent",
            'body'  => "R" . number_format($this->total, 2) . " invoice sent to {$this->clientName}.",
            'url'   => "/invoices/{$this->invoiceId}",
            'icon'  => 'invoice',
        ];
    }
}
