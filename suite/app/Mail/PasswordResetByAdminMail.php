<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetByAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $tempPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Xquisite password has been reset');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.password-reset-by-admin', with: [
            'user'         => $this->user,
            'tempPassword' => $this->tempPassword,
            'loginUrl'     => route('login'),
        ]);
    }
}
