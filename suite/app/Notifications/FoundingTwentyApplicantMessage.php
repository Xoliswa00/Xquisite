<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/**
 * Plain, personal email to a Founding 20 applicant. Sent to an on-demand
 * mail route (they are not users), from copy in FoundingTwentyMessages.
 */
class FoundingTwentyApplicantMessage extends MailNotification
{
    public function __construct(public string $subjectLine, public string $body)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)->subject($this->subjectLine)->greeting(' ');

        foreach (preg_split("/\n{2,}/", trim($this->body)) as $paragraph) {
            $message->line(new \Illuminate\Support\HtmlString(nl2br(e($paragraph))));
        }

        return $message->salutation('The Xquisite Creations team');
    }
}
