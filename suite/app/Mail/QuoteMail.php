<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Quote {$this->quote->reference} — {$this->quote->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.quote',
            with: [
                'quote'      => $this->quote,
                'acceptUrl'  => route('public.quotes.show', [$this->quote, $this->quote->acceptToken()]),
                'declineUrl' => route('public.quotes.decline', [$this->quote, $this->quote->acceptToken()]),
            ],
        );
    }
}
