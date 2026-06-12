<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DirectMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $fromName,
        public string $subject,
        public string $preview,
        public bool   $fromOwner,
        public int    $clientId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $context = $this->fromOwner ? 'your service provider' : 'your client';

        return (new MailMessage)
            ->subject("New message: {$this->subject}")
            ->greeting("Hello {$notifiable->name},")
            ->line("You have a new message from **{$this->fromName}** ({$context}).")
            ->line("> {$this->preview}")
            ->action('View Message', url($this->fromOwner ? '/portal/messages' : "/clients/{$this->clientId}/messages"))
            ->line('Log in to reply.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Message from {$this->fromName}",
            'body'  => $this->preview,
            'url'   => $this->fromOwner
                        ? '/portal/messages'
                        : "/clients/{$this->clientId}/messages",
            'icon'  => 'message',
        ];
    }
}
