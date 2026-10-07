<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Contact & Support Channels
    |--------------------------------------------------------------------------
    | Used in the demo banner, welcome email, and floating WhatsApp button.
    | Set WHATSAPP_NUMBER as the full international number without + or spaces.
    | e.g. 27821234567 for a South African number.
    |
    */

    'whatsapp_number'  => env('WHATSAPP_NUMBER', '27000000000'),
    'whatsapp_message' => env('WHATSAPP_MESSAGE', 'Hi! I\'d like to learn more about Xquisite.'),

    // The one support inbox shown to users everywhere (pages, emails, error
    // screens). Deliberately not tied to MAIL_FROM_ADDRESS: the send-from address
    // is often a no-reply or a different mailbox.
    'support_email'    => env('SUPPORT_EMAIL', 'support@xquisite.brightfinance-x.co.za'),
    'support_name'     => env('SUPPORT_NAME', 'Xquisite Support'),

];
