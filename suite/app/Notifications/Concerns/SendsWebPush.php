<?php

namespace App\Notifications\Concerns;

use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Shared push-notification support for every Notification class in the app.
 *
 * Every existing notification already hardcodes its channel list in via()
 * (e.g. `return ['database', 'mail'];`) — this doesn't touch that shape, it
 * gives each one a one-line way to opt in: replace the literal array with
 * `return $this->withWebPush(['database', 'mail'], $notifiable);`. Nothing
 * changes for a notifiable with no push subscription; the channel is only
 * appended when one exists, so this never adds a wasted delivery attempt.
 *
 * toWebPush() has a generic default built from the notification's own
 * toArray()/toDatabase() — nearly every notification in this app already
 * returns a {title, message, url} shape for its in-app/database record,
 * which is exactly what a push notification needs too. Override toWebPush()
 * in a class whose array shape doesn't carry a sensible title/message (see
 * AppointmentBookedNotification, CriticalLogAlert, ModuleRequestSubmitted /
 * ModuleRequestStatusChanged for examples).
 */
trait SendsWebPush
{
    protected function withWebPush(array $channels, $notifiable): array
    {
        if (method_exists($notifiable, 'pushSubscriptions') && $notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $data = match (true) {
            method_exists($this, 'toArray')    => (array) $this->toArray($notifiable),
            method_exists($this, 'toDatabase') => (array) $this->toDatabase($notifiable),
            default                             => [],
        };

        $message = (new WebPushMessage)
            ->title($data['title'] ?? config('app.name'))
            ->icon('/img/android-icon-192x192.png')
            ->body($data['message'] ?? '');

        if (!empty($data['url'])) {
            $message->data(['url' => $data['url']]);
        }

        return $message;
    }
}
