<?php

namespace App\Notifications;

use App\Modules\Property\Models\RentPayment;
use Illuminate\Notifications\Messages\MailMessage;

class RentDueReminderNotification extends MailNotification
{
    public function __construct(public RentPayment $rentPayment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $unitLabel = $this->rentPayment->unit?->property?->name . ' — Unit ' . $this->rentPayment->unit?->unit_number;
        $balance = (float) $this->rentPayment->amount_due - (float) $this->rentPayment->amount_paid;

        return (new MailMessage)
            ->subject("Rent due soon — {$unitLabel}")
            ->greeting("Hi {$notifiable->name},")
            ->line("This is a reminder that your rent for {$this->rentPayment->periodLabel()} is due on " . $this->rentPayment->due_date->format('d M Y') . '.')
            ->line('Amount due: R' . number_format($balance, 2))
            ->line("Property: {$unitLabel}")
            ->line('If you\'ve already paid, please disregard this reminder.');
    }

    public function toArray(object $notifiable): array
    {
        $balance = (float) $this->rentPayment->amount_due - (float) $this->rentPayment->amount_paid;

        return [
            'title'   => 'Rent due soon',
            'message' => 'R' . number_format($balance, 2) . ' due ' . $this->rentPayment->due_date->format('d M Y') . ' for ' . $this->rentPayment->periodLabel(),
            'url'     => $notifiable->tenant ? route('rent.payments', $notifiable->tenant->slug) : null,
            'icon'    => 'invoice',
        ];
    }
}
