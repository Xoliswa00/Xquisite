<?php

namespace App\Notifications;

use App\Models\PlatformInvoice;
use Illuminate\Notifications\Messages\MailMessage;

class BillingInvoiceCreatedNotification extends MailNotification
{

    public function __construct(public PlatformInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Platform Invoice {$this->invoice->invoice_number} â€” R" . number_format($this->invoice->amount, 2))
            ->line("Your platform invoice {$this->invoice->invoice_number} for R" . number_format($this->invoice->amount, 2) . " is due on {$this->invoice->due_date->format('d M Y')}.")
            ->action('View Invoice', url('/billing/' . $this->invoice->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => "Invoice {$this->invoice->invoice_number}",
            'message' => 'R' . number_format($this->invoice->amount, 2) . " due {$this->invoice->due_date->format('d M Y')}",
            'url'     => '/billing/' . $this->invoice->id,
            'icon'    => 'invoice',
        ];
    }
}
