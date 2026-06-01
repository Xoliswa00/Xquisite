<?php

namespace App\Mail;

use App\Modules\Property\Models\Renter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RenterPortalInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Renter $renter,
        public string $temporaryPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Renter Portal Access',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.renter-portal-invite',
            with: [
                'renter'    => $this->renter,
                'password'  => $this->temporaryPassword,
                'loginUrl'  => url('/rent/' . $this->renter->tenant?->slug . '/login'),
            ],
        );
    }
}
