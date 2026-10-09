<?php

namespace App\Support;

/**
 * User text placed inside a Markdown email (a notification ->line(), a
 * Markdown mail view).
 *
 * Blade's {{ }} only protects HTML. A signup name written as
 * `[Approve this signup](https://evil.example)` is still Markdown, and the
 * mail renderer turns it into a live link in an email that comes from us.
 *
 * Markdown::withSecuredEncoding() (on in AppServiceProvider) covers this, but
 * only when the mail view is compiled during a mail render. If the view was
 * compiled first by `php artisan view:cache` / `optimize`, the cached copy is
 * reused and that protection is silently skipped. Escaping the value here
 * does not depend on compile order, so use it for any value a stranger can
 * set (signup names, business names, public form fields).
 */
class MailText
{
    /** Backslash-escape the characters Markdown uses for links, images and emphasis. */
    public static function plain(?string $value): string
    {
        return preg_replace('/([\\\\\[\]()!*_`])/', '\\\\$1', (string) $value);
    }
}
