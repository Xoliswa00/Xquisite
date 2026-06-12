<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ClientQuoteSentNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $quoteNumber,
        public string $companyName,
        public float  $total,
        public int    $quoteId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You have a new quote from {$this->companyName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("{$this->companyName} has sent you quote **{$this->quoteNumber}** totalling **R" . number_format($this->total, 2) . "**.")
            ->action('View Quote', url("/quotes/{$this->quoteId}"))
            ->line('Please review and respond at your earliest convenience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "New quote from {$this->companyName}",
            'body'  => "Quote {$this->quoteNumber} — R" . number_format($this->total, 2) . " is awaiting your review.",
            'url'   => "/quotes/{$this->quoteId}",
            'icon'  => 'quote',
        ];
    }
}
