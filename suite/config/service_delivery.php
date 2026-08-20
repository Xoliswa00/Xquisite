<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public Service Request Form
    |--------------------------------------------------------------------------
    | The public "Request a Service" form (no login required) is tied to a
    | single tenant — Xquisite Creations' own account — rather than being
    | resolved per-tenant like the booking module's public portal. Change the
    | slug here (or via SERVICE_REQUEST_TENANT_SLUG) if that tenant's slug
    | ever changes.
    |
    */

    'public_request_tenant_slug' => env('SERVICE_REQUEST_TENANT_SLUG', 'xquisite-creations'),

];
