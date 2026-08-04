<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\Tenant\TenantContext;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // 2. Subdomain detection — {slug}.xquisite.co.za. Resolved even when
        // step 1 already found a tenant, so a disagreement between "who the
        // session belongs to" and "which subdomain is being visited" can be
        // detected rather than silently overridden.
        $subdomainTenantId = $this->resolveSubdomainTenantId($request);

        if (!$tenantId) {
            $tenantId = $subdomainTenantId;
        } elseif ($subdomainTenantId && $subdomainTenantId !== $tenantId) {
            // Precedence is unchanged (the authenticated user's own tenant
            // wins) — this only makes an existing implicit rule observable.
            $this->logTenantMismatch($request, $tenantId, $subdomainTenantId);
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

    private function resolveSubdomainTenantId(Request $request): ?int
    {
        $host      = $request->getHost();
        $appDomain = config('app.domain', 'xquisite.co.za');

        if (!str_ends_with($host, '.' . $appDomain)) {
            return null;
        }

        $subdomain = substr($host, 0, -strlen('.' . $appDomain));

        if (!$subdomain || $subdomain === 'www') {
            return null;
        }

        $tenant = Tenant::where('subdomain', $subdomain)
            ->orWhere('slug', $subdomain)
            ->where('is_active', true)
            ->first();

        return $tenant?->id;
    }

    /**
     * A session cookie leaking across tenant subdomains (e.g. a misconfigured
     * SESSION_DOMAIN in production) would look exactly like this: an
     * authenticated user whose own tenant differs from the subdomain they're
     * visiting. Precedence still favours the user's own tenant — this only
     * makes the disagreement observable instead of silent.
     */
    private function logTenantMismatch(Request $request, int $ownTenantId, int $subdomainTenantId): void
    {
        if ($request->hasSession() && $request->session()->get('tenant_mismatch_logged') === $subdomainTenantId) {
            return;
        }

        try {
            DB::table('system_logs')->insert([
                'level'      => 'WARNING',
                'message'    => "ResolveTenant: authenticated user's tenant ({$ownTenantId}) differs from visited subdomain's tenant ({$subdomainTenantId})",
                'context'    => json_encode([
                    'user_id'             => Auth::id(),
                    'own_tenant_id'       => $ownTenantId,
                    'subdomain_tenant_id' => $subdomainTenantId,
                    'host'                => $request->getHost(),
                ]),
                'user_id'    => Auth::id(),
                'ip_address' => $request->ip(),
                'url'        => $request->fullUrl(),
                'status'     => 'new',
                'source'     => 'suite',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // Never let logging break the request
        }

        if ($request->hasSession()) {
            $request->session()->put('tenant_mismatch_logged', $subdomainTenantId);
        }
    }
}
