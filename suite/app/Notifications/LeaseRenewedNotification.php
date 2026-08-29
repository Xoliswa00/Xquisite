<?php

namespace App\Notifications;

use App\Modules\Property\Models\Lease;
use Illuminate\Notifications\Messages\MailMessage;

class LeaseRenewedNotification extends MailNotification
{
    public function __construct(public Lease $lease, public float $oldRent) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $unitLabel = $this->lease->property?->name . ' — Unit ' . $this->lease->unit?->unit_number;
        $rentChanged = (float) $this->lease->monthly_rent !== $this->oldRent;

        $mail = (new MailMessage)
            ->subject("Your lease has been renewed — {$unitLabel}")
            ->greeting("Hi {$notifiable->name},")
            ->line("Your lease at {$unitLabel} has been renewed, now ending " . $this->lease->end_date->format('d M Y') . '.');

        if ($rentChanged) {
            $mail->line('New monthly rent: R' . number_format($this->lease->monthly_rent, 2) . ' (previously R' . number_format($this->oldRent, 2) . ')');
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Your lease has been renewed',
            'message' => 'New end date: ' . $this->lease->end_date->format('d M Y'),
            'url'     => $notifiable->tenant ? route('rent.lease', $notifiable->tenant->slug) : null,
            'icon'    => 'bell',
        ];
    }
}
