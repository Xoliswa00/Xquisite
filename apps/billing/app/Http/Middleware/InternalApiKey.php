<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InternalApiKey
{
    public function handle(Request $request, Closure $next)
    {
        $key = config('billing.internal_api_key');

        if (!$key || $request->header('X-Internal-Key') !== $key) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
