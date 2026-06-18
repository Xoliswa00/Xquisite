<?php

namespace App\Notifications;

use App\Models\PlatformInvoice;
use Barryvdh\DomPDF\Facade\Pdf;
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
        $invoice = $this->invoice->load('tenant.activeModules.platformModule');
        $amount  = number_format($invoice->amount, 2);

        $pdf = Pdf::loadView('billing.invoice-pdf', ['invoice' => $invoice]);
        $pdfContent = $pdf->output();

        return (new MailMessage)
            ->subject(“Invoice {$invoice->invoice_number} - R{$amount}”)
            ->line(“Hi {$notifiable->name},”)
            ->line(“Your platform invoice {$invoice->invoice_number} for R{$amount} has been generated and is due on {$invoice->due_date->format('d F Y')}.”)
            ->line(“Please find your invoice attached. You may also upload proof of payment directly from the billing page.”)
            ->action('View & Pay', url('/billing'))
            ->attachData($pdfContent, “invoice-{$invoice->invoice_number}.pdf”, ['mime' => 'application/pdf']);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => “Invoice {$this->invoice->invoice_number}”,
            'message' => 'R' . number_format($this->invoice->amount, 2) . “ due {$this->invoice->due_date->format('d M Y')}”,
            'url'     => '/billing',
            'icon'    => 'invoice',
        ];
    }
}
