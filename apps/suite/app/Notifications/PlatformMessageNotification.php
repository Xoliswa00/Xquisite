<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Notifications\Messages\MailMessage;

class PlatformMessageNotification extends MailNotification
{
    public function __construct(
        public Tenant $tenant,
        public string $preview,
        public bool $fromPlatform
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->fromPlatform
            ? 'New message from Xquisite Support'
            : "New message from {$this->tenant->name}";

        return (new MailMessage)
            ->subject($subject)
            ->line($this->preview)
            ->action('View Message', $this->fromPlatform
                ? url('/portal/messages')
                : url('/admin/tenants/' . $this->tenant->id . '/messages')
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => $this->fromPlatform
                ? 'New message from Xquisite'
                : "Message from {$this->tenant->name}",
            'message' => substr($this->preview, 0, 100),
            'url'     => $this->fromPlatform
                ? '/portal/messages'
                : '/admin/tenants/' . $this->tenant->id . '/messages',
            'icon'    => 'bell',
        ];
    }
}
