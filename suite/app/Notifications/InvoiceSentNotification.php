<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class InvoiceSentNotification extends MailNotification
{

    public function __construct(public string $invoiceRef, public string $clientName, public float $amount) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoice {$this->invoiceRef} sent to {$this->clientName}")
            ->line("Invoice {$this->invoiceRef} for R" . number_format($this->amount, 2) . " was sent to {$this->clientName}.")
            ->action('View Quotes', url('/quotes'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => "Invoice {$this->invoiceRef} sent",
            'message' => "Sent to {$this->clientName} â€” R" . number_format($this->amount, 2),
            'url'     => '/quotes',
            'icon'    => 'invoice',
        ];
    }
}
