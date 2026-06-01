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

    'support_email'    => env('SUPPORT_EMAIL', env('MAIL_FROM_ADDRESS', 'support@xquisite.co.za')),
    'support_name'     => env('SUPPORT_NAME', 'Xquisite Support'),

];
