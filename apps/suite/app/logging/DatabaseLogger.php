<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminate\Support\Facades\DB;

class DatabaseLogger
{
    public function __invoke(array $config)
    {
        $logger = new Logger('database');

        $logger->pushHandler(new class extends AbstractProcessingHandler {
            protected function write(array $record): void
            {
               $requestId = app()->bound('request_id')
    ? app('request_id')
    : null;

DB::table('system_logs')->insert([
    'level' => $record['level_name'],
    'message' => $record['message'],
    'context' => json_encode($record['context'] ?? []),

    'request_id' => $requestId,
    'user_id' => auth()->id(),
    'ip_address' => request()->ip(),
    'url' => request()->fullUrl(),

    'created_at' => now(),
]);
            }
        });

        return $logger;
    }
}