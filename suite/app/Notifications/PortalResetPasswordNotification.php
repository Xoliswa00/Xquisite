<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * Shared password-reset email for the renter, customer, and contractor portals —
 * each guard's model overrides sendPasswordResetNotification() to build a
 * tenant-slug-scoped reset URL and pass it here along with a label for copy.
 */
class PortalResetPasswordNotification extends MailNotification
{
    public function __construct(
        public string $resetUrl,
        public string $portalLabel,
        public string $tenantName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Reset your {$this->tenantName} password")
            ->line("You're receiving this email because we received a password reset request for your {$this->portalLabel} account with {$this->tenantName}.")
            ->action('Reset Password', $this->resetUrl)
            ->line('This password reset link will expire in 60 minutes.')
            ->line("If you did not request a password reset, no further action is required.");
    }
}
