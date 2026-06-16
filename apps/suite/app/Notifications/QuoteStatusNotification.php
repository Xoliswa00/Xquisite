<?php

namespace App\Notifications;

use App\Models\Quote;
use Illuminate\Notifications\Messages\MailMessage;

class QuoteStatusNotification extends MailNotification
{

    public function __construct(public Quote $quote, public string $event) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Quote {$this->quote->reference} â€” {$this->event}")
            ->line("Quote {$this->quote->reference} has been {$this->event}.")
            ->action('View Quote', url('/quotes/' . $this->quote->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => "Quote {$this->quote->reference} {$this->event}",
            'message' => "Quote \"{$this->quote->title}\" has been {$this->event}.",
            'url'     => '/quotes/' . $this->quote->id,
            'icon'    => 'quote',
        ];
    }
}
