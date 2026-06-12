<?php

namespace App\Mail;

use App\Modules\Booking\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
  public function __construct(
        public Appointment $appointment,
        public string $recipient = 'customer', // 'customer' | 'booker'
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->recipient === 'booker'
            ? 'Booking confirmed — ' . $this->appointment->customer->full_name
            : 'Your appointment is confirmed';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: $this->recipient === 'booker'
                ? 'emails.appointments.confirmation-booker'
                : 'emails.appointments.confirmation-customer',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
