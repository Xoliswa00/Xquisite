<?php

namespace App\Mail;

use App\Modules\Booking\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** "Your quote is ready": price and time from the client's inspiration photos. */
class QuoteReadyEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your quote from ' . ($this->appointment->tenant?->name ?? config('app.name')));
    }

    public function content(): Content
    {
        return new Content(view: 'emails.appointments.quote-ready', with: [
            'myBookingsUrl' => $this->appointment->tenant ? route('book.my-bookings', $this->appointment->tenant->slug) : url('/'),
        ]);
    }
}
