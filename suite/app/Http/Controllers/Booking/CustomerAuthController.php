<?php

namespace App\Http\Controllers\Booking;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Booking\Models\Customer;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function logout(string $slug, Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('book.index', $slug);
    }
}
