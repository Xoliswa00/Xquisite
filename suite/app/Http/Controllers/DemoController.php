<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Request;

class DemoController extends Controller
{
    public function show()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('demo');
    }

    public function login(): RedirectResponse
    {
        $key = 'demo-login:' . Request::ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return redirect()->route('welcome')->with('error', 'Too many demo requests. Please try again shortly.');
        }

        RateLimiter::hit($key, 60);

        $demoTenant = Tenant::where('is_demo', true)->first();

        if (! $demoTenant) {
            return redirect()->route('welcome')->with('error', 'Demo is being set up. Please check back shortly.');
        }

        $demoUser = User::where('tenant_id', $demoTenant->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'tenant-owner'))
            ->first();

        if (! $demoUser) {
            return redirect()->route('welcome')->with('error', 'Demo is being set up. Please check back shortly.');
        }

        if (! $demoUser->hasVerifiedEmail()) {
            $demoUser->markEmailAsVerified();
        }

        Auth::login($demoUser);
        session()->regenerate();

        return redirect()->route('dashboard')->with('demo_welcome', true);
    }
}
