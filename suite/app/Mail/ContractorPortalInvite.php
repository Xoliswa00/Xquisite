<?php

namespace App\Mail;

use App\Modules\Property\Models\Contractor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractorPortalInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Contractor $contractor,
        public string $temporaryPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Contractor Portal Access',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contractor-portal-invite',
            with: [
                'contractor' => $this->contractor,
                'password'   => $this->temporaryPassword,
                'loginUrl'   => url('/contractor/' . $this->contractor->tenant?->slug . '/login'),
            ],
        );
    }
}
