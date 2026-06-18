<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Billing App Connection
    |--------------------------------------------------------------------------
    | URL and key for the internal billing API.
    | Set BILLING_URL and BILLING_INTERNAL_KEY in .env.
    | Both apps must share the same BILLING_INTERNAL_KEY value.
    |
    */

    'url'          => env('BILLING_URL', 'http://localhost:8001'),
    'internal_key' => env('BILLING_INTERNAL_KEY'),

];
