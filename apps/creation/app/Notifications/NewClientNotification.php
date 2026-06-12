<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NewClientNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $clientName,
        public string $clientEmail,
        public int    $clientId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New client added — {$this->clientName}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A new client **{$this->clientName}** ({$this->clientEmail}) has been added to your account.")
            ->action('View Client', url("/clients/{$this->clientId}"))
            ->line('Their portal invitation has been sent.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New client: ' . $this->clientName,
            'body'  => "{$this->clientEmail} has been added as a client.",
            'url'   => "/clients/{$this->clientId}",
            'icon'  => 'client',
        ];
    }
}
