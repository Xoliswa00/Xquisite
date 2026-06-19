<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Illuminate\Support\Facades\DB;

class DatabaseLogger
{
    private const ALERT_LEVELS = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

    public function __invoke(array $config)
    {
        $logger = new Logger('database');

        $logger->pushHandler(new class extends AbstractProcessingHandler {
            private const ALERT_LEVELS = ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];

            protected function write(LogRecord $record): void
            {
                try {
                    $requestId = app()->bound('request_id') ? app('request_id') : null;
                    $levelName = $record->level->name;
                    $url       = request()->fullUrl();
                    $path      = request()->path();

                    $source = match (true) {
                        str_starts_with($path, 'book/')          => 'booking-portal',
                        str_starts_with($path, 'admin/')         => 'admin',
                        str_starts_with($path, 'portal/')        => 'client-portal',
                        str_starts_with($path, 'shop/')          => 'shop',
                        default                                  => 'suite',
                    };

                    $id = DB::table('system_logs')->insertGetId([
                        'level'      => $levelName,
                        'message'    => $record->message,
                        'context'    => json_encode($record->context),
                        'request_id' => $requestId,
                        'user_id'    => \Illuminate\Support\Facades\Auth::id(),
                        'ip_address' => request()->ip(),
                        'url'        => $url,
                        'status'     => 'new',
                        'source'     => $source,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    if (in_array(strtoupper($levelName), self::ALERT_LEVELS)) {
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

                    $log = \App\Models\Modules\Core\Models\SystemLog::find($logId);
                    if (!$log) {
                        return;
                    }

                    // Use anonymous notifiable so we don't need a User model
                    \Illuminate\Support\Facades\Notification::route('mail', $adminEmail)
                        ->notify(new \App\Notifications\CriticalLogAlert($log));

                } catch (\Throwable) {
                    // Silently fail — notification errors must never break logging
                }
            }
        });

        return $logger;
    }
}
