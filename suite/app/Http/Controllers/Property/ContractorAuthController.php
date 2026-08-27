<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\AuditService;
use App\Services\Security\LoginThrottleService;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class ContractorAuthController extends Controller
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
        return view('property.contractor-portal.auth.login', compact('tenant', 'slug'));
    }

    public function login(string $slug, Request $request)
    {
        $this->resolveTenant($slug);

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('contractor')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            AuditService::log(
                action: 'contractor.login',
                entityType: 'Contractor',
                entityId: Auth::guard('contractor')->id(),
                meta: ['tenant_slug' => $slug],
            );

            return redirect()->route('contractor.portal', $slug);
        }

        AuditService::log(
            action: 'contractor.login_failed',
            entityType: 'Contractor',
            meta: ['email' => $request->input('email'), 'tenant_slug' => $slug],
        );
        LoginThrottleService::recordFailure($request->ip(), 'contractor');

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
    }

    public function showForgotPassword(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        return view('property.contractor-portal.auth.forgot-password', compact('tenant', 'slug'));
    }

    public function sendResetLink(string $slug, Request $request)
    {
        $this->resolveTenant($slug);

        $request->validate(['email' => 'required|email']);

        $status = Password::broker('contractors')->sendResetLink($request->only('email'));

        AuditService::log(
            action: $status === Password::RESET_LINK_SENT ? 'contractor.password_reset_requested' : 'contractor.password_reset_request_failed',
            entityType: 'Contractor',
            meta: ['email' => $request->input('email'), 'tenant_slug' => $slug, 'status' => $status],
        );

        if ($status !== Password::RESET_LINK_SENT) {
            LoginThrottleService::recordFailure($request->ip(), 'contractor-password-reset');
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'A password reset link has been sent to your email.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $slug, string $token, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        $email  = $request->query('email');
        return view('property.contractor-portal.auth.reset-password', compact('tenant', 'slug', 'token', 'email'));
    }

    public function resetPassword(string $slug, Request $request)
    {
        $this->resolveTenant($slug);

        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('contractors')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($contractor, $password) {
                $contractor->forceFill(['password' => $password])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            AuditService::log(
                action: 'contractor.password_reset_completed',
                entityType: 'Contractor',
                meta: ['email' => $request->input('email'), 'tenant_slug' => $slug],
            );

            return redirect()->route('contractor.login', $slug)->with('success', 'Your password has been reset. You can now sign in.');
        }

        AuditService::log(
            action: 'contractor.password_reset_failed',
            entityType: 'Contractor',
            meta: ['email' => $request->input('email'), 'tenant_slug' => $slug, 'status' => $status],
        );
        LoginThrottleService::recordFailure($request->ip(), 'contractor-password-reset');

        return back()->withErrors(['email' => __($status)]);
    }

    public function logout(string $slug, Request $request)
    {
        AuditService::log(
            action: 'contractor.logout',
            entityType: 'Contractor',
            entityId: Auth::guard('contractor')->id(),
            meta: ['tenant_slug' => $slug],
        );

        Auth::guard('contractor')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('contractor.login', $slug);
    }
}
