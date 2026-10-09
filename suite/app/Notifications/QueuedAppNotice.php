<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * An AppNotice that is delivered by the queue instead of inside the request.
 * Use it when the request that triggers the notice belongs to someone else
 * (a failed sign-in, a webhook), so a slow push endpoint can't slow or time
 * that request, and its duration can't reveal that a notice was sent.
 */
class QueuedAppNotice extends AppNotice implements ShouldQueue
{
    public int $tries = 3;
}
