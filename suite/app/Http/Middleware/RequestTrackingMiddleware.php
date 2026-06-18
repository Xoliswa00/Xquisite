<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestTrackingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->header('X-Request-ID') ?? (string) Str::uuid();

        $request->headers->set('X-Request-ID', $requestId);

        app()->instance('request_id', $requestId);

        logger()->withContext([
            'request_id' => $requestId,
            'ip'         => $request->ip(),
            'url'        => $request->fullUrl(),
            'user_id'    => auth()->id(),
        ]);

        $response = $next($request);

        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
