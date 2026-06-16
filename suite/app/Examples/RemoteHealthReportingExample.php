<?php

/**
 * Example: How to integrate health reporting in a remote hosted app
 * 
 * This file shows how a separate hosted app can report its health back to the owner platform.
 */

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Routing\Controller;

class RemoteHealthController extends Controller
{
    /**
     * Example method to report health to the owner platform
     * 
     * Call this periodically (every 5-10 minutes via a scheduled job)
     */
    public static function reportToOwnerPlatform(): JsonResponse
    {
        // Gather your app's health data
        $health = [
            'status' => self::isAppHealthy() ? 'up' : 'down',
            'uptime' => self::calculateUptime(),
            'version' => config('app.version', '1.0'),
            'db_connection' => self::checkDatabaseConnection(),
            'queue_status' => self::checkQueueStatus(),
            'error_message' => self::getLastError(),
        ];

        // Send to owner platform
        $response = \Illuminate\Support\Facades\Http::timeout(5)
            ->withHeaders([
                'Authorization' => 'Bearer ' . env('OWNER_PLATFORM_TOKEN'),
            ])
            ->post(
                env('OWNER_PLATFORM_URL') . '/api/health-report',
                $health
            );

        return $response->json();
    }

    private static function isAppHealthy(): bool
    {
        return self::checkDatabaseConnection() && 
               self::checkQueueStatus() !== 'failed';
    }

    private static function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function checkQueueStatus(): string
    {
        // Check if queue is processing jobs
        try {
            return 'running'; // or 'failed' if there are issues
        } catch (\Exception $e) {
            return 'error';
        }
    }

    private static function calculateUptime(): float
    {
        // Calculate your app's uptime percentage
        // This could be based on error logs or downtime records
        return 99.9;
    }

    private static function getLastError(): ?string
    {
        // Get the last error from your logs
        return null;
    }
}

/**
 * HOW TO USE:
 * 
 * 1. Set these env variables in your .env file:
 *    OWNER_PLATFORM_URL=https://your-owner-platform.com
 *    OWNER_PLATFORM_TOKEN=your-api-token-from-owner-platform
 * 
 * 2. Create a scheduled command that calls:
 *    RemoteHealthController::reportToOwnerPlatform();
 * 
 * 3. Schedule it to run every 5-10 minutes in your console/kernel.php:
 *    $schedule->call(function() {
 *        RemoteHealthController::reportToOwnerPlatform();
 *    })->everyFiveMinutes();
 * 
 * 4. The owner platform will receive and log all health reports
 */
