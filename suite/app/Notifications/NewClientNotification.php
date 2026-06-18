<?php

namespace App\Notifications;

use App\Models\Client;
use Illuminate\Notifications\Messages\MailMessage;

class NewClientNotification extends MailNotification
{

    public function __construct(public Client $client) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Client: {$this->client->name}")
            ->line("A new client \"{$this->client->name}\" has been added.")
            ->action('View Clients', url('/clients'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'New client added',
            'message' => "\"{$this->client->name}\" was added as a client.",
            'url'     => '/clients/' . $this->client->id,
            'icon'    => 'client',
        ];
    }
}
