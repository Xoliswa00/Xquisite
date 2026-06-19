<?php

namespace App\Notifications;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTenantRegistered extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Tenant $tenant,
        public readonly User   $owner,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("New Signup: {$this->tenant->name}")
            ->greeting('New tenant registered!')
            ->line("{$this->owner->name} just signed up as **{$this->tenant->name}**.")
            ->line("Industry: " . ucfirst($this->tenant->industry ?? 'Not specified'))
            ->line("Email: {$this->owner->email}")
            ->line("Trial ends: " . ($this->tenant->trial_ends_at?->format('d M Y') ?? 'N/A'))
            ->action('View Tenant', route('admin.tenants.show', $this->tenant))
            ->line('Log in to the platform admin to manage this tenant.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'New signup: ' . $this->tenant->name,
            'message' => "{$this->owner->name} ({$this->owner->email}) just registered.",
            'url'     => route('admin.tenants.show', $this->tenant),
            'type'    => 'new_tenant',
        ];
    }
}
