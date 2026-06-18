<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * Base for all notifications that deliver via mail.
 * Queued so mail failures never crash a user-facing request.
 */
abstract class MailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function failed(\Throwable $e): void
    {
        \Log::error('Notification delivery failed', [
            'notification' => static::class,
            'error'        => $e->getMessage(),
        ]);
    }
}
