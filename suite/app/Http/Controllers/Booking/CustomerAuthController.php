<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Booking\Models\Customer;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $this->resolveTenant($slug);

        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('customer')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('book.index', $slug));
        }

        return back()->withErrors(['email' => 'These credentials do not match our records.'])->withInput();
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
            'phone'    => 'nullable|string|max:50',
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

    // ── Account claim (phone-based) ─────────────────────────────────────────

    public function showClaim(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        return view('booking.auth.claim', compact('tenant', 'slug'));
    }

    public function lookupByPhone(string $slug, Request $request)
    {
        $tenant = $this->resolveTenant($slug);

        $request->validate(['phone' => 'required|string|max:50']);

        // Normalise to digits only, then to SA international format
        $raw = preg_replace('/[^0-9]/', '', $request->phone);
        if (strlen($raw) === 9)                                  $raw = '27' . $raw;
        if (strlen($raw) === 10 && str_starts_with($raw, '0'))  $raw = '27' . substr($raw, 1);

        $customer = Customer::where('tenant_id', $tenant->id)
            ->whereNotNull('phone')
            ->get()
            ->first(function ($c) use ($raw) {
                $s = preg_replace('/[^0-9]/', '', $c->phone ?? '');
                if (strlen($s) === 9)                                $s = '27' . $s;
                if (strlen($s) === 10 && str_starts_with($s, '0'))  $s = '27' . substr($s, 1);
                return $s === $raw;
            });

        if (!$customer) {
            return back()->withErrors(['phone' => 'No client record found with that number. Please register as a new customer instead.'])->withInput();
        }

        if ($customer->password) {
            return back()->withErrors(['phone' => 'This account already has a login set up. Please sign in with your email address.'])->withInput();
        }

        session(['claim_customer_id' => $customer->id]);
        return redirect()->route('book.claim.setup', $slug);
    }

    public function showClaimSetup(string $slug)
    {
        $tenant     = $this->resolveTenant($slug);
        $customerId = session('claim_customer_id');

        if (!$customerId) {
            return redirect()->route('book.claim', $slug);
        }

        $customer = Customer::where('tenant_id', $tenant->id)->findOrFail($customerId);
        return view('booking.auth.claim-setup', compact('tenant', 'slug', 'customer'));
    }

    public function completeClaimSetup(string $slug, Request $request)
    {
        $tenant     = $this->resolveTenant($slug);
        $customerId = session('claim_customer_id');

        if (!$customerId) {
            return redirect()->route('book.claim', $slug);
        }

        $customer = Customer::where('tenant_id', $tenant->id)->findOrFail($customerId);

        $request->validate([
            'email'                 => ['required', 'email', Rule::unique('customers', 'email')->ignore($customer->id)],
            'password'              => 'required|string|min:8|confirmed',
        ]);

        $customer->update([
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        session()->forget('claim_customer_id');
        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('book.index', $slug)
            ->with('success', "Welcome, {$customer->name}! Your account is ready — you can now sign in any time.");
    }

    public function logout(string $slug, Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('book.index', $slug);
    }
}
