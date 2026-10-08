<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Booking\Models\Customer;
use App\Notifications\CustomerLoginReplacedNotification;
use App\Rules\SouthAfricanPhoneNumber;
use App\Services\AuditService;
use App\Services\Security\LoginThrottleService;
use App\Services\Tenant\TenantContext;
use App\Support\SignInIdentifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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

        $request->validate([
            'login'    => 'nullable|string|max:190',
            'password' => 'required|string',
        ]);

        // An email address or a cell number. Read through SignInIdentifier so
        // this and the pause middleware can never disagree about who is signing in.
        $login = SignInIdentifier::typed($request);

        if ($login === null) {
            return back()->withErrors(['login' => 'Enter your email address or cell number.']);
        }

        $phone = str_contains($login, '@') ? null : Customer::normalisePhone($login);

        // Not an email and not a cell number: say so, and don't count it as a wrong password.
        if (! str_contains($login, '@') && ! $phone) {
            return back()
                ->withErrors(['login' => 'Enter your email address or your 10-digit cell number, like 082 123 4567.'])
                ->withInput($request->only('login'));
        }

        $key = SignInIdentifier::canonical($login);
        [$customer, $ambiguous] = $this->customerForSignIn($tenant, $login, $phone, (string) $request->input('password'));

        if ($ambiguous) {
            return back()
                ->withErrors(['login' => "More than one login uses this cell number and password, so we can't tell which is yours. Sign in with your email address, or ask {$tenant->name} for a new login link."])
                ->withInput($request->only('login'));
        }

        if ($customer) {
            Auth::guard('customer')->login($customer, $request->boolean('remember'));
            $request->session()->regenerate();
            LoginThrottleService::recordSuccess($request->ip(), 'customer', $key);

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
            meta: ['login' => $key, 'tenant_slug' => $slug],
        );
        $left = LoginThrottleService::recordFailure($request->ip(), 'customer', 'login', $key, ['tenant_slug' => $slug]);

        return back()
            ->withErrors(['login' => 'These details do not match our records.' . LoginThrottleService::warning($left)])
            ->withInput($request->only('login'));
    }

    /** How many logins sharing one cell number are checked. A family, not a crowd. */
    private const MAX_SHARED_CELL_LOGINS = 5;

    /**
     * Many clients have no email address, so a cell number works as the sign-in
     * name too. Cell numbers are not unique, so the few logins on that number
     * are each tried against the password.
     *
     * @return array{0: ?Customer, 1: bool} the customer, and whether the password
     *                                      fits more than one login on the number
     */
    private function customerForSignIn(Tenant $tenant, string $login, ?string $phone, string $password): array
    {
        $query = Customer::where('tenant_id', $tenant->id)->whereNotNull('password')->orderBy('id');

        $candidates = $phone
            ? $query->where('phone_normalised', $phone)->limit(self::MAX_SHARED_CELL_LOGINS)->get()
            // Compared in lower case so it behaves the same on every database.
            : $query->whereRaw('LOWER(email) = ?', [mb_strtolower($login)])->limit(1)->get();

        $matches = $candidates->filter(fn (Customer $c) => Hash::check($password, $c->password))->values();

        // Same amount of work whether or not anyone matched, so the response
        // time doesn't say which emails and numbers have a login.
        if ($candidates->isEmpty()) {
            Hash::make($password);
        }

        if ($matches->count() > 1) {
            return [null, true];
        }

        if ($customer = $matches->first()) {
            if (Hash::needsRehash($customer->password)) {
                $customer->forceFill(['password' => Hash::make($password)])->save();
            }
        }

        return [$customer, false];
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
    private function staleSetupLink(string $slug, Tenant $tenant, Customer $customer)
    {
        $message = "That link no longer works. Ask {$tenant->name} to send you a new one.";

        // Someone who already has a login is better off on the sign-in page,
        // where they can also try the password they know.
        return $customer->password
            ? redirect()->route('book.login', $slug)->withErrors(['link' => $message])
            : redirect()->route('book.claim', $slug)->withErrors(['link' => $message]);
    }

    /**
     * Whether this customer can sign in with their cell number alone: there is a
     * usable number on file and nobody else at the business already signs in with it.
     */
    private function canSignInWithCellAlone(Customer $customer): bool
    {
        $phone = Customer::normalisePhone($customer->phone);

        return $phone !== null && ! Customer::where('tenant_id', $customer->tenant_id)
            ->where('phone_normalised', $phone)
            ->whereNotNull('password')
            ->whereKeyNot($customer->id)
            ->exists();
    }

    public function showClaimSetup(string $slug, Customer $customer, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 404);

        if (! $customer->acceptsSetupLink($request->query('v'))) {
            return $this->staleSetupLink($slug, $tenant, $customer);
        }

        return view('booking.auth.claim-setup', [
            'tenant'       => $tenant,
            'slug'         => $slug,
            'customer'     => $customer,
            'submitUrl'    => $request->fullUrl(),
            'cellAlone'    => $this->canSignInWithCellAlone($customer),
            'hasCell'      => Customer::normalisePhone($customer->phone) !== null,
        ]);
    }

    public function completeClaimSetup(string $slug, Customer $customer, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        abort_unless((int) $customer->tenant_id === (int) $tenant->id, 404);

        if (! $customer->acceptsSetupLink($request->query('v'))) {
            return $this->staleSetupLink($slug, $tenant, $customer);
        }

        $hadLogin  = filled($customer->password);
        $cellAlone = $this->canSignInWithCellAlone($customer);
        $hasCell   = Customer::normalisePhone($customer->phone) !== null;

        // A link that replaces a forgotten password changes the password only.
        // Whoever holds it can't also move the login to a different address.
        $rules = ['password' => 'required|string|min:8|confirmed'];

        if (! $hadLogin) {
            // Email is optional when the cell number alone is enough to sign in with.
            $rules['email'] = [
                $cellAlone ? 'nullable' : 'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->where('tenant_id', $tenant->id)->ignore($customer->id),
            ];
        }

        $request->validate($rules, [
            'email.unique'   => 'This email already has a login. Sign in or reset your password, or use a different email here.',
            'email.required' => $hasCell
                ? 'Someone else already signs in with this cell number, so please add an email address to sign in with.'
                : 'Please add an email address. There is no cell number on your record to sign in with.',
        ]);

        // One use only, even if the same link is submitted twice at once: the row
        // is locked and the link checked again before anything is changed.
        $used = DB::transaction(function () use ($customer, $request, $hadLogin) {
            $locked = Customer::withoutGlobalScopes()->lockForUpdate()->find($customer->id);

            if (! $locked || ! $locked->acceptsSetupLink($request->query('v'))) {
                return false;
            }

            $locked->forceFill([
                'email'                 => (! $hadLogin && $request->filled('email')) ? $request->email : $locked->email,
                'password'              => Hash::make($request->password),
                'remember_token'        => Str::random(60),   // signs out any "remember me" on other devices
                'setup_link_version'    => $locked->setup_link_version + 1,
                'setup_link_expires_at' => null,
            ])->save();

            return true;
        });

        if (! $used) {
            return $this->staleSetupLink($slug, $tenant, $customer->refresh());
        }

        $customer->refresh();

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        AuditService::log(
            action: 'customer.claim_completed',
            entityType: 'Customer',
            entityId: $customer->id,
            meta: ['tenant_slug' => $slug, 'replaced_existing_login' => $hadLogin],
        );

        // Staff can create these links, so the customer is always told when one
        // has replaced a password they already had.
        if ($hadLogin && $customer->email) {
            try {
                $customer->notify(new CustomerLoginReplacedNotification($tenant->name, now()->format('H:i'), $tenant->phone));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $with = match (true) {
            $customer->email && $cellAlone => 'your email address or cell number',
            (bool) $customer->email        => 'your email address',
            default                        => 'your cell number',
        };

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
                // A new password also ends any "remember me" on other devices.
                $customer->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
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
