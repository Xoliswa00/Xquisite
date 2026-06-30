<?php

namespace App\Notifications;

use App\Models\MonitoredInstance;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstanceDownNotification extends Notification
{
    public function __construct(
        public readonly MonitoredInstance $instance,
        public readonly ?string $errorMessage = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->error()
            ->subject("🔴 [{$this->instance->name}] Instance is DOWN")
            ->line("**{$this->instance->name}** failed its health check and appears to be down.")
            ->line("**URL:** {$this->instance->url}")
            ->line("**Time:** " . now()->format('d M Y H:i:s') . ' UTC');

        if ($this->errorMessage) {
            $message->line("**Error:** {$this->errorMessage}");
        }

        return $message
            ->action('View Monitoring Dashboard', url('/monitoring'))
            ->line('Consecutive failures: ' . $this->instance->consecutive_failures);
    }
}
