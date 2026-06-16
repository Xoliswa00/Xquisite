<?php

namespace App\Notifications;

use App\Models\Modules\Core\Models\SystemLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalLogAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly SystemLog $log) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("[{$this->log->source}] Critical Error — {$this->log->level}")
            ->line("A **{$this->log->level}** event was captured in **{$this->log->source}**.")
            ->line("**Message:** {$this->log->message}")
            ->line("**URL:** " . ($this->log->url ?? 'N/A'))
            ->line("**Time:** {$this->log->created_at->format('d M Y H:i:s')}")
            ->action('View in Admin', url('/admin/logs/' . $this->log->id))
            ->line('Log in to acknowledge or assign this error.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'log_id'  => $this->log->id,
            'level'   => $this->log->level,
            'message' => $this->log->message,
            'source'  => $this->log->source,
            'url'     => $this->log->url,
        ];
    }
}
