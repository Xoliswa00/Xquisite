<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Property\Models\Renter;
use App\Services\AuditService;
use App\Services\Security\LoginThrottleService;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RenterAuthController extends Controller
{
    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    public function showLogin(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        return view('property.portal.auth.login', compact('tenant', 'slug'));
    }

    public function login(string $slug, Request $request)
    {
        $this->resolveTenant($slug);

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('renter')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            AuditService::log(
                action: 'renter.login',
                entityType: 'Renter',
                entityId: Auth::guard('renter')->id(),
                meta: ['tenant_slug' => $slug],
            );

            return redirect()->route('rent.portal', $slug);
        }

        AuditService::log(
            action: 'renter.login_failed',
            entityType: 'Renter',
            meta: ['email' => $request->input('email'), 'tenant_slug' => $slug],
        );
        LoginThrottleService::recordFailure($request->ip(), 'renter');

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
    }

    public function logout(string $slug, Request $request)
    {
        AuditService::log(
            action: 'renter.logout',
            entityType: 'Renter',
            entityId: Auth::guard('renter')->id(),
            meta: ['tenant_slug' => $slug],
        );

        Auth::guard('renter')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('rent.login', $slug);
    }
}
