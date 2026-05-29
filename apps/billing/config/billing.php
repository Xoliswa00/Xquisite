<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Internal API Key
    |--------------------------------------------------------------------------
    | This key secures the /api/internal/* endpoints that other Xquisite
    | apps (e.g. suite) use to push subscription requests into billing.
    | Set BILLING_INTERNAL_KEY in .env — keep it secret.
    |
    */

    'internal_api_key' => env('BILLING_INTERNAL_KEY'),

];
