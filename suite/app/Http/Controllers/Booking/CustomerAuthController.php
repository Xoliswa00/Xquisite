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
use Illuminate\Validation\Rule;

class CustomerAuthController extends Controller
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
        return view('booking.auth.login', compact('tenant', 'slug'));
    }

    public function login(string $slug, Request $request)
    {
        $tenant = $this->resolveTenant($slug);

        // "login" is an email address or a cell number. "email" is still accepted
        // so an older open sign-in page keeps working.
        $request->validate([
            'login'    => 'required_without:email|nullable|string|max:190',
            'password' => 'required|string',
        ]);

        $login    = trim((string) ($request->input('login') ?? $request->input('email')));
        $customer = $this->customerForSignIn($tenant, $login, (string) $request->input('password'));

        if ($customer) {
            Auth::guard('customer')->login($customer, $request->boolean('remember'));
            $request->session()->regenerate();
            LoginThrottleService::recordSuccess($request->ip(), 'customer', $login);

            AuditService::log(
                action: 'customer.login',
                entityType: 'Customer',
                entityId: $customer->id,
                meta: ['tenant_slug' => $slug],
            );

            return redirect()->intended(route('book.index', $slug));
        }

        AuditService::log(
            action: 'customer.login_failed',
            entityType: 'Customer',
            meta: ['login' => $login, 'tenant_slug' => $slug],
        );
        $left = LoginThrottleService::recordFailure($request->ip(), 'customer', 'login', $login, ['tenant_slug' => $slug]);

        return back()
            ->withErrors(['login' => 'These details do not match our records.' . LoginThrottleService::warning($left)])
            ->withInput($request->only('login'));
    }

    /**
     * Many clients have no email address, so a cell number works as the sign-in
     * name too. Cell numbers are not unique, so every matching client with a
     * login is tried against the password.
     */
    private function customerForSignIn(Tenant $tenant, string $login, string $password): ?Customer
    {
        $candidates = collect();

        if (str_contains($login, '@')) {
            // Compared in lower case so it behaves the same on every database.
            $candidates = Customer::where('tenant_id', $tenant->id)->whereRaw('LOWER(email) = ?', [mb_strtolower($login)])->whereNotNull('password')->get();
        } elseif ($phone = Customer::normalisePhone($login)) {
            $candidates = Customer::where('tenant_id', $tenant->id)->whereNotNull('password')->whereNotNull('phone')
                ->get()
                ->filter(fn (Customer $c) => Customer::normalisePhone($c->phone) === $phone);
        }

        foreach ($candidates as $candidate) {
            if (Hash::check($password, $candidate->password)) {
                return $candidate;
            }
        }

        // Same amount of work whether or not anyone matched, so the response
        // time doesn't say which emails and numbers have a login.
        if ($candidates->isEmpty()) {
            Hash::make($password);
        }

        return null;
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
            'email'    => ['required', 'email', Rule::unique('customers', 'email')->where('tenant_id', $tenant->id)],
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

    /** A link that has been used, cancelled, replaced or has run out. */
    private function staleSetupLink(string $slug, Customer $customer)
    {
        if ($customer->password) {
            return redirect()->route('book.login', $slug)
                ->with('success', 'That link has already been used. Sign in with your email address or cell number.');
        }

        return redirect()->route('book.claim', $slug)
            ->withErrors(['link' => 'That setup link no longer works. Ask for a new one.']);
    }

    public function showClaimSetup(string $slug, Customer $customer, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 404);

        if (! $customer->acceptsSetupLink($request->query('v'))) {
            return $this->staleSetupLink($slug, $customer);
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

        if (! $customer->acceptsSetupLink($request->query('v'))) {
            return $this->staleSetupLink($slug, $customer);
        }

        // Email is optional for anyone with a cell number on file: they can sign
        // in with the number. Without either there would be nothing to sign in with.
        $request->validate([
            'email'    => [
                Customer::normalisePhone($customer->phone) ? 'nullable' : 'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->where('tenant_id', $tenant->id)->ignore($customer->id),
            ],
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique'   => 'This email already has a login. Sign in or reset your password, or use a different email here.',
            'email.required' => 'Please add an email address. There is no cell number on your record to sign in with.',
        ]);

        $hadLogin = filled($customer->password);

        $customer->update([
            'email'    => $request->filled('email') ? $request->email : $customer->email,
            'password' => Hash::make($request->password),
        ]);
        $customer->cancelSetupLink();   // one use only

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        AuditService::log(
            action: 'customer.claim_completed',
            entityType: 'Customer',
            entityId: $customer->id,
            meta: ['tenant_slug' => $slug, 'replaced_existing_login' => $hadLogin],
        );

        $with = $customer->email ? 'your email address or cell number' : 'your cell number';

        return redirect()->route('book.index', $slug)
            ->with('success', "Welcome, {$customer->name}! Your login is ready. Next time, sign in with {$with}.");
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
