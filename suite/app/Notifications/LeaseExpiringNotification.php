<?php

namespace App\Notifications;

use App\Modules\Property\Models\Lease;
use Illuminate\Notifications\Messages\MailMessage;

class LeaseExpiringNotification extends MailNotification
{
    public function __construct(public Lease $lease, public int $daysLeft) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $unitLabel = $this->lease->property?->name . ' — Unit ' . $this->lease->unit?->unit_number;

        return (new MailMessage)
            ->subject("Lease expiring in {$this->daysLeft} day(s) — {$unitLabel}")
            ->line("The lease for {$this->lease->renter?->name} at {$unitLabel} ends in {$this->daysLeft} day(s), on " . $this->lease->end_date->format('d M Y') . '.')
            ->line('Decide whether to renew, let it lapse to month-to-month, or start the move-out process.')
            ->action('View Lease', route('leases.show', $this->lease));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => "Lease expiring in {$this->daysLeft} day(s)",
            'message' => "{$this->lease->renter?->name} — {$this->lease->property?->name} Unit {$this->lease->unit?->unit_number}",
            'url'     => route('leases.show', $this->lease),
            'icon'    => 'bell',
        ];
    }
}
