<?php

namespace App\Http\Controllers\Api;

use App\Models\HealthCheckLog;
use App\Models\MonitoredInstance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class HealthReportController extends Controller
{
    /**
     * Get health status - endpoint for external apps to poll
     */
    public function show(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Bearer token required'
            ], 401);
        }

        $instance = MonitoredInstance::where('api_token', $token)->first();

        if (!$instance) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => 'Instance not found'
            ], 401);
        }

        return response()->json([
            'instance_id' => $instance->id,
            'status' => $instance->status,
            'uptime_percentage' => $instance->uptime_percentage,
            'last_check' => $instance->last_check_at?->toIso8601String(),
            'consecutive_failures' => $instance->consecutive_failures,
        ]);
    }

    /**
     * Receive health report from remote instance
     */
    public function store(Request $request): JsonResponse
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Bearer token required'
            ], 401);
        }

        $instance = MonitoredInstance::where('api_token', $token)->first();

        if (!$instance) {
            return response()->json([
                'error' => 'Invalid token',
                'message' => 'Instance not found'
            ], 401);
        }

        $validated = $request->validate([
            'status' => 'required|in:up,down',
            'uptime' => 'nullable|numeric|min:0|max:100',
            'version' => 'nullable|string',
            'db_connection' => 'nullable|boolean',
            'queue_status' => 'nullable|string',
            'error_message' => 'nullable|string',
        ]);

        $isUp = $validated['status'] === 'up';
        $error = $validated['error_message'] ?? null;

        HealthCheckLog::create([
            'monitored_instance_id' => $instance->id,
            'status' => $validated['status'],
            'response_time_ms' => null,
            'error_message' => $error,
            'metadata' => [
                'version' => $validated['version'] ?? null,
                'db_connection' => $validated['db_connection'] ?? null,
                'queue_status' => $validated['queue_status'] ?? null,
            ],
            'checked_at' => now(),
        ]);

        if ($isUp) {
            $instance->update([
                'status' => 'up',
                'last_check_at' => now(),
                'consecutive_failures' => 0,
                'uptime_percentage' => $validated['uptime'] ?? $instance->uptime_percentage,
            ]);
        } else {
            $instance->increment('consecutive_failures');
            $instance->update([
                'status' => 'down',
                'last_check_at' => now(),
                'last_error_at' => now(),
                'last_error_message' => $error,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Health report received',
            'instance_id' => $instance->id,
        ]);
    }
}
