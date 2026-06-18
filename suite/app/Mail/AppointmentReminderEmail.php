<?php

namespace App\Mail;

use App\Modules\Booking\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string      $reminderType = '24h', // '24h' or '1h'
    ) {}

    public function envelope(): Envelope
    {
        $when = $this->reminderType === '1h' ? 'in 1 hour' : 'tomorrow';

        return new Envelope(
            subject: "Reminder: Your appointment is {$when}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-reminder',
        );
    }
}
