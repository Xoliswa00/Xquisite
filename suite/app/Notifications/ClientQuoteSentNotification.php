<?php

namespace App\Notifications;

use App\Models\Quote;
use Illuminate\Notifications\Messages\MailMessage;

class ClientQuoteSentNotification extends MailNotification
{

    public function __construct(public Quote $quote) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("You have a new quote â€” {$this->quote->reference}")
            ->line("A new quote \"{$this->quote->title}\" for R" . number_format($this->quote->total, 2) . " has been sent to you.")
            ->action('View Portal', url('/portal/dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => "New quote: {$this->quote->reference}",
            'message' => "\"{$this->quote->title}\" â€” R" . number_format($this->quote->total, 2),
            'url'     => '/portal/dashboard',
            'icon'    => 'quote',
        ];
    }
}
