<?php

namespace App\Http\Middleware;

use App\Models\PlatformModule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleActive
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenant = auth()->user()?->tenant;

        if (!$tenant) {
            abort(403, 'No tenant context.');
        }

        if (!$tenant->hasModule($module)) {
            $name = PlatformModule::where('key', $module)->value('name') ?? ucfirst($module);

            return redirect()->route('dashboard')->with(
                'error',
                "{$name} is not enabled for your account. Contact support to activate it."
            );
        }

        return $next($request);
    }
}
