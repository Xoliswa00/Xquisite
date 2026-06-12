<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientPortalInvite extends Mailable
{
    use Queueable, SerializesModels;

      public $invitation; // the invitation data

    /**
     * Create a new message instance.
     */
    public function __construct($invitation)
    {
        $this->invitation = $invitation;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('You are invited to your Client Portal')
                    ->markdown('emails.client.invite')
                    ->with([
                        'invitation' => $this->invitation,
                    ]);
    }
}
