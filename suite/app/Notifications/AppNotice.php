<?php

namespace App\Notifications;

use App\Notifications\Concerns\SendsWebPush;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppNotice extends Notification
{
    use Queueable, SendsWebPush;

    public function __construct(
        public string $title,
        public string $message,
        public ?string $url = null,
        public string $level = 'info'
    ) {
    }

    public function via($notifiable): array
    {
        return $this->withWebPush(['database'], $notifiable);
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'level' => $this->level,
        ];
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
