<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestTrackingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

       // 1. Get or generate request ID
        $requestId = $request->header('X-Request-ID') ?? (string) Str::uuid();

        // 2. Bind to request lifecycle
        $request->headers->set('X-Request-ID', $requestId);

        // 3. Share globally (very important)
        app()->instance('request_id', $requestId);

        // 4. Attach to Laravel logger context
        logger()->withContext([
            'request_id' => $requestId,
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
            'user_id' => auth()->id(),
        ]);
        return $next($request);


           // 5. Attach header to response (debugging + API tracing)
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
