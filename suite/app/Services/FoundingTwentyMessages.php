<?php

namespace App\Services;

use App\Models\FoundingTwentyApplication;

/**
 * The words we send applicants at each stage, in one place so email and the
 * admin's WhatsApp links say exactly the same thing and can be reviewed together.
 * Each returns ['subject' => string, 'body' => string] (body is plain text, blank
 * line between paragraphs).
 */
class FoundingTwentyMessages
{
    public const TYPES = ['received', 'decision', 'activation', 'conversion'];

    /** Which timestamp column records that a message type has gone out. */
    public const COLUMNS = [
        'received' => 'received_notified_at',
        'decision' => 'decision_notified_at',
        'activation' => 'activation_nudge_sent_at',
        'conversion' => 'conversion_offer_sent_at',
    ];

    public static function for(string $type, FoundingTwentyApplication $a): array
    {
        return match ($type) {
            'received' => self::received($a),
            'decision' => self::decision($a),
            'activation' => self::activation($a),
            'conversion' => self::conversion($a),
        };
    }

    public static function received(FoundingTwentyApplication $a): array
    {
        $days = config('founding_twenty.decision_within_days');

        return [
            'subject' => 'We received your Founding 20 application',
            'body' => "Hi {$a->firstName()},\n\n"
                . "Thank you for applying to the Xquisite Creations Founding 20 for {$a->business_name}. We have your application and we read every one.\n\n"
                . "You will hear from us within {$days} days, whether or not you are selected. If you have a question in the meantime, just reply to this message.",
        ];
    }

    public static function decision(FoundingTwentyApplication $a): array
    {
        return match ($a->status) {
            'selected', 'converted' => [
                'subject' => "You're in: Founding 20 for {$a->business_name}",
                'body' => "Hi {$a->firstName()},\n\n"
                    . "Good news. {$a->business_name} has been selected for the Xquisite Creations Founding 20.\n\n"
                    . 'To hold your spot, please use this link to pay the fully refundable R' . number_format((float) config('founding_twenty.deposit_amount'), 0) . " deposit and upload your proof of payment:\n"
                    . route('founding-twenty.reserve', [$a, $a->reservationToken()]) . "\n\n"
                    . 'Once that is confirmed we set you up, and your 3 free months begin. Reply here if anything is unclear.',
            ],
            'waitlisted' => [
                'subject' => "Your Founding 20 application: you're on the waiting list",
                'body' => "Hi {$a->firstName()},\n\n"
                    . "Thank you for applying for {$a->business_name}. All 20 places are spoken for right now, and we have put you on the waiting list. If a place opens up, you are next in line and we will contact you straight away.\n\n"
                    . 'You do not need to do anything. If your situation changes or you would like to talk, reply to this message.',
            ],
            default => [
                'subject' => 'Your Founding 20 application',
                'body' => "Hi {$a->firstName()},\n\n"
                    . "Thank you for applying for {$a->business_name} and for the time you put into your answers. We have chosen the 20 businesses we can help most in this first group, and yours is not one of them this time.\n\n"
                    . 'This is not a comment on your business. If you would like to be told when we open to more businesses, reply "keep me posted" and we will.',
            ],
        };
    }

    public static function activation(FoundingTwentyApplication $a): array
    {
        return [
            'subject' => "How is Xquisite going for {$a->business_name}?",
            'body' => "Hi {$a->firstName()},\n\n"
                . "It has been a little while since we set up {$a->business_name}, and we want to make sure you are getting something useful out of Xquisite already. Is there anything that is stuck, confusing or missing?\n\n"
                . 'Reply with what you tried and where it went wrong, and we will sort it out with you, usually the same day.',
        ];
    }

    public static function conversion(FoundingTwentyApplication $a): array
    {
        $price = number_format((float) config('founding_twenty.monthly_price'), 0);

        return [
            'subject' => "Your free months are nearly over: what happens next",
            'body' => "Hi {$a->firstName()},\n\n"
                . "Your 3 free months with Xquisite Creations for {$a->business_name} finish in about two weeks.\n\n"
                . "If Xquisite has been useful, you can carry on for R{$price} a month. If it has not, you can stop with no charge and no hard feelings, and your R"
                . number_format((float) config('founding_twenty.deposit_amount'), 0) . " deposit is refunded either way.\n\n"
                . 'Either way, we would like to hear what worked and what did not. Reply here or tell us on your final check-in.',
        ];
    }
}
