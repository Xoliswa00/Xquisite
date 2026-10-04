<?php

namespace App\Mail;

use App\Modules\Booking\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * "Time for your next ..." with a one-click, no-login opt-out (signed link),
 * so stopping the messages is never harder than receiving them.
 */
class RebookReminderEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Appointment $appointment, public readonly string $bookUrl) {}

    public function envelope(): Envelope
    {
        $services = $this->appointment->services->pluck('name')->join(', ', ' & ') ?: 'appointment';

        return new Envelope(subject: "Time for your next {$services}?");
    }

    public function content(): Content
    {
        $tenant = $this->appointment->tenant;

        return new Content(view: 'emails.appointments.rebook-reminder', with: [
            'optOutUrl' => $tenant
                ? URL::signedRoute('book.rebook-reminders.opt-out', [$tenant->slug, $this->appointment->customer_id])
                : null,
        ]);
    }
}
