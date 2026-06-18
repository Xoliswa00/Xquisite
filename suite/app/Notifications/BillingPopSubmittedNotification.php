<?php

namespace App\Notifications;

use App\Models\PlatformInvoice;
use Illuminate\Notifications\Messages\MailMessage;

class BillingPopSubmittedNotification extends MailNotification
{
    public function __construct(public PlatformInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $tenant = $this->invoice->tenant;

        return (new MailMessage)
            ->subject("POP Submitted — {$this->invoice->invoice_number} ({$tenant->name})")
            ->line("{$tenant->name} has uploaded proof of payment for invoice {$this->invoice->invoice_number} (R" . number_format($this->invoice->amount, 2) . ").")
            ->line("Please review and mark the invoice as paid once confirmed.")
            ->action('Review Invoice', url("/admin/billing/{$tenant->id}"));
    }

    public function toArray(object $notifiable): array
    {
        $tenant = $this->invoice->tenant;

        return [
            'title'   => "POP submitted — {$this->invoice->invoice_number}",
            'message' => "{$tenant->name} uploaded proof of payment. R" . number_format($this->invoice->amount, 2),
            'url'     => "/admin/billing/{$tenant->id}",
            'icon'    => 'invoice',
        ];
    }
}
