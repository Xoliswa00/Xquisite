<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Illuminate\Support\Facades\DB;

class DatabaseLogger
{
    private const ALERT_LEVELS = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    public function __invoke(array $config)
    {
        $logger = new Logger('database');

        $logger->pushHandler(new class extends AbstractProcessingHandler {
            private const ALERT_LEVELS = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

            protected function write(array $record): void
            {
                try {
                    $id = DB::table('system_logs')->insertGetId([
                        'level'      => $record['level_name'],
                        'message'    => $record['message'],
                        'context'    => json_encode($record['context'] ?? []),
                        'user_id'    => auth()->id(),
                        'ip_address' => request()->ip(),
                        'url'        => request()->fullUrl(),
                        'status'     => 'new',
                        'source'     => 'billing',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (in_array(strtoupper($record['level_name']), self::ALERT_LEVELS)) {
                        $this->alertAdmin($id);
                    }

                } catch (\Throwable) {
                    // Never let logging break the app
                }
            }

            private function alertAdmin(int $logId): void
            {
                try {
                    $adminEmail = config('mail.from.address');
                    if (!$adminEmail) {
                        return;
                    }

                    $log = \App\Models\SystemLog::find($logId);
                    if (!$log) {
                        return;
                    }

                    \Illuminate\Support\Facades\Notification::route('mail', $adminEmail)
                        ->notify(new \App\Notifications\CriticalLogAlert($log));

                } catch (\Throwable) {}
            }
        });

        return $logger;
    }
}
