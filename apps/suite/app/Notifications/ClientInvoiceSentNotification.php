<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

class ClientInvoiceSentNotification extends MailNotification
{

    public function __construct(public string $invoiceRef, public float $amount) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoice {$this->invoiceRef} â€” R" . number_format($this->amount, 2))
            ->line("You have received invoice {$this->invoiceRef} for R" . number_format($this->amount, 2) . ".")
            ->action('View Portal', url('/portal/dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => "Invoice {$this->invoiceRef}",
            'message' => 'R' . number_format($this->amount, 2) . ' â€” please review and pay.',
            'url'     => '/portal/dashboard',
            'icon'    => 'invoice',
        ];
    }
}
