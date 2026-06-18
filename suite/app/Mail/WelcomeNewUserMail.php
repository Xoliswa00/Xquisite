<?php

namespace App\Mail;

use App\Models\BillingSetting;
use App\Models\PlatformModule;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeNewUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Xquisite — here\'s what you can activate',
        );
    }

    public function content(): Content
    {
        $activeModules = PlatformModule::active()->visible()->ordered()->get();
        $betaModules   = PlatformModule::beta()->visible()->ordered()->get();

        return new Content(
            view: 'emails.welcome-new-user',
            with: [
                'user'          => $this->user,
                'activeModules' => $activeModules,
                'betaModules'   => $betaModules,
                'loginUrl'      => route('login'),
                'modulesUrl'    => route('settings.modules.index'),
                'whatsappUrl'   => 'https://wa.me/' . (BillingSetting::get('whatsapp_number') ?? config('contact.whatsapp_number')) . '?text=' . urlencode(BillingSetting::get('whatsapp_message') ?? config('contact.whatsapp_message')),
            ],
        );
    }
}
