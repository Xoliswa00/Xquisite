<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\Tenant\TenantContext;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Clear any static state from a previous request (Octane / queue worker safety)
        TenantContext::clear();

        $tenantId = null;

        // 1. From authenticated user (primary path — admin panel)
        if (Auth::check()) {
            $tenantId = Auth::user()->tenant_id;
        }

        // 2. Subdomain detection — {slug}.xquisite.co.za
        if (!$tenantId) {
            $host      = $request->getHost();
            $appDomain = config('app.domain', 'xquisite.co.za');

            if (str_ends_with($host, '.' . $appDomain)) {
                $subdomain = substr($host, 0, -strlen('.' . $appDomain));

                if ($subdomain && $subdomain !== 'www') {
                    $tenant = Tenant::where('subdomain', $subdomain)
                        ->orWhere('slug', $subdomain)
                        ->where('is_active', true)
                        ->first();

                    if ($tenant) {
                        $tenantId = $tenant->id;
                    }
                }
            }
        }

        // 3. Custom domain detection
        if (!$tenantId) {
            try {
                $host   = $request->getHost();
                $tenant = Tenant::where('custom_domain', $host)
                    ->where('custom_domain_verified', true)
                    ->where('is_active', true)
                    ->first();

                if ($tenant) {
                    $tenantId = $tenant->id;
                }
            } catch (QueryException $e) {
                // Only swallow "unknown column" errors during a migration deployment window
                if ($e->getCode() !== '42S22') {
                    Log::error('ResolveTenant: unexpected DB error on custom_domain lookup', ['error' => $e->getMessage()]);
                }
            }
        }

        if ($tenantId) {
            TenantContext::set($tenantId);
        }

        return $next($request);
    }
}
