<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * "Was this you?" Sent to the holder of an account whose sign-in has just been
 * paused after repeated wrong passwords (see SignInPauseNotifier).
 */
class SignInPausedNotification extends MailNotification
{
    public function __construct(
        public string $accountLabel,
        public string $businessName,
        public int $attempts,
        public int $minutes,
        public string $at,
        public string $resetUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Was this you? Sign-in to your {$this->businessName} account was paused")
            ->line("The wrong password was entered {$this->attempts} times for your {$this->accountLabel} login with {$this->businessName} at {$this->at} today, so we paused sign-in for {$this->minutes} minutes.")
            ->line('If that was you, there is nothing you need to do. Wait a few minutes and try again, or choose a new password with the button below.')
            ->action('Choose a new password', $this->resetUrl)
            ->line('If it was not you, those attempts did not get into your account. Choose a new password to be safe.');
    }
}
