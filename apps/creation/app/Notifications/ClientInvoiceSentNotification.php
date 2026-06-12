<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ClientInvoiceSentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $invoiceNumber,
        public string $companyName,
        public float  $total,
        public string $dueDate,
        public int    $invoiceId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Invoice {$this->invoiceNumber} from {$this->companyName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have a new invoice **{$this->invoiceNumber}** from {$this->companyName} for **R" . number_format($this->total, 2) . "**.")
            ->line("Due date: **{$this->dueDate}**")
            ->action('View Invoice', url("/invoices/{$this->invoiceId}"))
            ->line('Please arrange payment before the due date.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Invoice {$this->invoiceNumber} received",
            'body'  => "R" . number_format($this->total, 2) . " due {$this->dueDate} from {$this->companyName}.",
            'url'   => "/invoices/{$this->invoiceId}",
            'icon'  => 'invoice',
        ];
    }
}
