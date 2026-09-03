<?php

namespace App\Http\Middleware;

use App\Models\MonitoredInstance;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bearer-token gate for the cross-project monitoring endpoints (/ingest/logs,
 * /api/health-report, /api/health-status). These are CSRF-exempt and carry no
 * session — a reporter app authenticates purely with the api_token issued when
 * its MonitoredInstance row was created on /admin/monitoring (routed through
 * MonitoringController, gated by can:manage-tenants).
 *
 * On success the resolved instance is stashed on the request so controllers can
 * read it without repeating the lookup:
 *   $request->attributes->get('monitored_instance')
 */
class EnsureMonitoredInstance
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Bearer token required',
            ], 401);
        }

        // api_token has no encrypted cast on the model — a direct column match
        // against the raw incoming token is correct here (see MonitoredInstance
        // migration note: the ->encrypted() call on that column is a no-op,
        // Blueprint has no such modifier, so the column is stored plaintext).
        $instance = MonitoredInstance::where('api_token', $token)
            ->where('is_active', true)
            ->first();

        if (! $instance) {
            return response()->json([
                'error'   => 'Invalid token',
                'message' => 'Instance not found',
            ], 401);
        }

        $request->attributes->set('monitored_instance', $instance);

        return $next($request);
    }
}
