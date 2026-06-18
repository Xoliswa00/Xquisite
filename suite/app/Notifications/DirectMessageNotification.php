<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Notifications\Messages\MailMessage;

class DirectMessageNotification extends MailNotification
{

    public function __construct(
        public Client $client,
        public string $preview,
        public bool $fromOwner
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->fromOwner
            ? "New message from your service provider"
            : "New message from {$this->client->name}";

        $url = $this->fromOwner
            ? url('/portal/messages')
            : url('/clients/' . $this->client->id . '/messages');

        return (new MailMessage)
            ->subject($subject)
            ->line($this->preview)
            ->action('View Message', $url);
    }

    public function toArray(object $notifiable): array
    {
        $url = $this->fromOwner
            ? '/portal/messages'
            : '/clients/' . $this->client->id . '/messages';

        return [
            'title'   => $this->fromOwner ? 'New message from your provider' : "Message from {$this->client->name}",
            'message' => substr($this->preview, 0, 100),
            'url'     => $url,
            'icon'    => 'bell',
        ];
    }
}
