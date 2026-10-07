<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Booking\Models\Customer;
use App\Rules\SouthAfricanPhoneNumber;
use App\Services\AuditService;
use App\Services\Security\LoginThrottleService;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class CustomerAuthController extends Controller
{
    public const CLAIM_LINK_DAYS = 7;

    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    public function showLogin(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        return view('booking.auth.login', compact('tenant', 'slug'));
    }

    public function login(string $slug, Request $request)
    {
        $this->resolveTenant($slug);

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('customer')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            LoginThrottleService::recordSuccess($request->ip(), 'customer', $request->input('email'));

            AuditService::log(
                action: 'customer.login',
                entityType: 'Customer',
                entityId: Auth::guard('customer')->id(),
                meta: ['tenant_slug' => $slug],
            );

            return redirect()->intended(route('book.index', $slug));
        }

        AuditService::log(
            action: 'customer.login_failed',
            entityType: 'Customer',
            meta: ['email' => $request->input('email'), 'tenant_slug' => $slug],
        );
        $left = LoginThrottleService::recordFailure($request->ip(), 'customer', 'login', $request->input('email'), ['tenant_slug' => $slug]);

        return back()->withErrors(['email' => 'These credentials do not match our records.' . LoginThrottleService::warning($left)])->withInput();
    }

    public function showRegister(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        return view('booking.auth.register', compact('tenant', 'slug'));
    }

    public function register(string $slug, Request $request)
    {
        $tenant = $this->resolveTenant($slug);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:customers,email',
            'phone'    => ['nullable', new SouthAfricanPhoneNumber],
            'password' => 'required|string|min:8|confirmed',
        ]);

        $customer = Customer::create([
            'tenant_id' => $tenant->id,
            'name'      => $request->name,
            'email'     => $request->email,
            'phone'     => $request->phone,
            'password'  => Hash::make($request->password),
            'is_active' => true,
        ]);

        Auth::guard('customer')->login($customer);

        return redirect()->route('book.index', $slug)->with('success', "Welcome, {$customer->name}! Your account is ready.");
    }

    // ── Account claim (setup link sent by the business) ─────────────────────

    public function showClaim(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        return view('booking.auth.claim', compact('tenant', 'slug'));
    }

    /**
     * The link the business sends a customer who has no login yet. Signed and
     * time-limited, and dead as soon as a password has been set.
     */
    public static function claimSetupUrl(Customer $customer, string $slug): string
    {
        return URL::temporarySignedRoute(
            'book.claim.setup',
            now()->addDays(self::CLAIM_LINK_DAYS),
            ['slug' => $slug, 'customer' => $customer->id],
        );
    }

    private function alreadySetUp(string $slug)
    {
        return redirect()->route('book.login', $slug)
            ->with('success', 'Your login is already set up. Sign in with your email address.');
    }

    public function showClaimSetup(string $slug, Customer $customer, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 404);

        if ($customer->password) {
            return $this->alreadySetUp($slug);
        }

        return view('booking.auth.claim-setup', [
            'tenant'    => $tenant,
            'slug'      => $slug,
            'customer'  => $customer,
            'submitUrl' => $request->fullUrl(),
        ]);
    }

    public function completeClaimSetup(string $slug, Customer $customer, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 404);

        if ($customer->password) {
            return $this->alreadySetUp($slug);
        }

        $request->validate([
            'email'                 => ['required', 'email', Rule::unique('customers', 'email')->ignore($customer->id)],
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $customer->update([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        AuditService::log(
            action: 'customer.claim_completed',
            entityType: 'Customer',
            entityId: $customer->id,
            meta: ['tenant_slug' => $slug],
        );

        return redirect()->route('book.index', $slug)
            ->with('success', "Welcome, {$customer->name}! Your account is ready. You can now sign in any time.");
    }

    // ── Forgot / reset password ──────────────────────────────────────────────

    public function showForgotPassword(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        return view('booking.auth.forgot-password', compact('tenant', 'slug'));
    }

    public function sendResetLink(string $slug, Request $request)
    {
        $this->resolveTenant($slug);

        $request->validate(['email' => 'required|email']);

        $status = Password::broker('customers')->sendResetLink($request->only('email'));

        AuditService::log(
            action: $status === Password::RESET_LINK_SENT ? 'customer.password_reset_requested' : 'customer.password_reset_request_failed',
            entityType: 'Customer',
            meta: ['email' => $request->input('email'), 'tenant_slug' => $slug, 'status' => $status],
        );

        // Asking again too soon is impatience, not a failed attempt.
        if (! in_array($status, [Password::RESET_LINK_SENT, Password::RESET_THROTTLED], true)) {
            LoginThrottleService::recordFailure($request->ip(), 'customer', 'reset', $request->input('email'), ['tenant_slug' => $slug]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'A password reset link has been sent to your email.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(string $slug, string $token, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        $email  = $request->query('email');
        return view('booking.auth.reset-password', compact('tenant', 'slug', 'token', 'email'));
    }

    public function resetPassword(string $slug, Request $request)
    {
        $this->resolveTenant($slug);

        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($customer, $password) {
                $customer->forceFill(['password' => $password])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            AuditService::log(
                action: 'customer.password_reset_completed',
                entityType: 'Customer',
                meta: ['email' => $request->input('email'), 'tenant_slug' => $slug],
            );

            return redirect()->route('book.login', $slug)->with('success', 'Your password has been reset. You can now sign in.');
        }

        AuditService::log(
            action: 'customer.password_reset_failed',
            entityType: 'Customer',
            meta: ['email' => $request->input('email'), 'tenant_slug' => $slug, 'status' => $status],
        );
        LoginThrottleService::recordFailure($request->ip(), 'customer', 'reset', $request->input('email'), ['tenant_slug' => $slug]);

        return back()->withErrors(['email' => __($status)]);
    }

    public function logout(string $slug, Request $request)
    {
        AuditService::log(
            action: 'customer.logout',
            entityType: 'Customer',
            entityId: Auth::guard('customer')->id(),
            meta: ['tenant_slug' => $slug],
        );

        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('book.index', $slug);
    }
}
