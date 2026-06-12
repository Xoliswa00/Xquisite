<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class QuoteStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $quoteNumber,
        public string $status,
        public string $clientName,
        public int    $quoteId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = ucfirst($this->status);

        return (new MailMessage)
            ->subject("Quote {$this->quoteNumber} — {$statusLabel}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Quote **{$this->quoteNumber}** for {$this->clientName} has been {$this->status}.")
            ->action('View Quote', url("/quotes/{$this->quoteId}"))
            ->line('Log in to your dashboard for full details.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'       => "Quote {$this->quoteNumber} {$this->status}",
            'body'        => "Quote for {$this->clientName} has been {$this->status}.",
            'url'         => "/quotes/{$this->quoteId}",
            'icon'        => 'quote',
            'status'      => $this->status,
        ];
    }
}
