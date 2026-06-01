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
            ->where('role', 'owner')
            ->first();

        if (! $demoUser) {
            return redirect()->route('welcome')->with('error', 'Demo is being set up. Please check back shortly.');
        }

        Auth::login($demoUser);

        session(['demo_mode' => true]);

        return redirect()->route('dashboard')->with('demo_welcome', true);
    }
}
