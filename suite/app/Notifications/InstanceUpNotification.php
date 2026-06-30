<?php

namespace App\Notifications;

use App\Models\MonitoredInstance;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InstanceUpNotification extends Notification
{
    public function __construct(
        public readonly MonitoredInstance $instance
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->success()
            ->subject("🟢 [{$this->instance->name}] Instance recovered")
            ->line("**{$this->instance->name}** is back online and responding to health checks.")
            ->line("**URL:** {$this->instance->url}")
            ->line("**Recovered at:** " . now()->format('d M Y H:i:s') . ' UTC')
            ->action('View Monitoring Dashboard', url('/monitoring'));
    }
}
