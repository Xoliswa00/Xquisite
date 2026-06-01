<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'industry'      => ['nullable', 'string', 'max:100'],
            'industry_other' => ['nullable', 'string', 'max:100', 'required_if:industry,other'],
            'name'          => ['required', 'string', 'max:255'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password'      => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $slug = $this->uniqueSlug($request->business_name);

        $industry = $request->industry === 'other'
            ? $request->industry_other
            : $request->industry;

        $tenant = Tenant::create([
            'name'          => $request->business_name,
            'slug'          => $slug,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'industry'      => $industry,
            'plan'          => 'starter',
            'is_active'     => true,
            'trial_ends_at' => now()->addDays(14),
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'tenant_id' => $tenant->id,
            'role'      => 'client',
        ]);

        event(new Registered($user));

        Mail::to($user->email)->queue(new WelcomeEmail($user, $tenant));

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
