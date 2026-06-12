<?php

namespace App\Notifications;

use App\Models\PlatformInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillingInvoiceCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public PlatformInvoice $invoice) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Platform Invoice ' . $this->invoice->invoice_number)
            ->greeting('Hi ' . $notifiable->name . ',')
            ->line('Your ' . ucfirst($this->invoice->plan) . ' plan invoice for **R' . number_format($this->invoice->amount, 2) . '** is ready.')
            ->line('Due date: **' . $this->invoice->due_date->format('d M Y') . '**')
            ->action('View Invoice & Pay', url('/billing'))
            ->line('Please pay by the due date to avoid service interruption.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'  => 'invoice',
            'title' => 'Invoice ' . $this->invoice->invoice_number,
            'body'  => 'R' . number_format($this->invoice->amount, 2) . ' due by ' . $this->invoice->due_date->format('d M Y') . '.',
            'url'   => '/billing',
        ];
    }
}
