<?php

namespace App\Notifications;

use App\Modules\Property\Models\RentPayment;
use Illuminate\Notifications\Messages\MailMessage;

class RentPaymentReceivedNotification extends MailNotification
{
    public function __construct(public RentPayment $rentPayment) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $unitLabel = $this->rentPayment->unit?->property?->name . ' — Unit ' . $this->rentPayment->unit?->unit_number;
        $isFull = $this->rentPayment->status === 'paid';
        $balance = (float) $this->rentPayment->amount_due - (float) $this->rentPayment->amount_paid;

        $mail = (new MailMessage)
            ->subject(($isFull ? 'Payment received' : 'Partial payment received') . " — {$unitLabel}")
            ->greeting("Hi {$notifiable->name},")
            ->line('We\'ve recorded your rent payment for ' . $this->rentPayment->periodLabel() . '.')
            ->line('Amount received: R' . number_format((float) $this->rentPayment->amount_paid, 2));

        if ($isFull) {
            $mail->line('This period is now fully paid. Thank you!');
        } else {
            $mail->line('Outstanding balance: R' . number_format($balance, 2));
        }

        if ($notifiable->tenant) {
            $mail->action('View Receipt', route('rent.payments.receipt', [$notifiable->tenant->slug, $this->rentPayment]));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $isFull = $this->rentPayment->status === 'paid';

        return [
            'title'   => $isFull ? 'Payment received' : 'Partial payment received',
            'message' => 'R' . number_format((float) $this->rentPayment->amount_paid, 2) . ' for ' . $this->rentPayment->periodLabel(),
            'url'     => $notifiable->tenant ? route('rent.payments', $notifiable->tenant->slug) : null,
            'icon'    => 'payment',
        ];
    }
}
