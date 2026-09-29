<?php

return [
    'enabled' => env('ANALYTICS_ENABLED', true),

    // Page views and clicks older than this are deleted daily.
    'retention_days' => 180,

    // Never tracked: back office, machine endpoints, and files.
    'excluded_prefixes' => ['admin', 'api', 'ingest', 'js-error', 'up', 't', 'storage', 'build', 'payfast', 'webhooks'],
];
