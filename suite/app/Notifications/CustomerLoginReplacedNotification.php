<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sent to a customer whose existing password was replaced through a login link
 * the business created. Staff can create that link, so the customer is always
 * told when it has been used.
 */
class CustomerLoginReplacedNotification extends MailNotification
{
    public function __construct(
        public string $businessName,
        public string $at,
        public ?string $businessPhone = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $contact = $this->businessPhone ? " on {$this->businessPhone}" : '';

        return (new MailMessage)
            ->subject("Your {$this->businessName} password was changed")
            ->line("A new password was set for your online login with {$this->businessName} at {$this->at} today, using a login link the business created for you.")
            ->line('If that was you, there is nothing more to do.')
            ->line("If it was not you, contact {$this->businessName}{$contact} straight away and ask them to send you a new login link.");
    }
}
