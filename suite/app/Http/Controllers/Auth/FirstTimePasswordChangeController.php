<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class FirstTimePasswordChangeController extends Controller
{
    public function create(): View
    {
        if (!auth()->user()->needsPasswordChange()) {
            abort(404);
        }

        return view('auth.change-password-first-time');
    }

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->needsPasswordChange()) {
            abort(404);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        auth()->user()->markPasswordChanged();

        return redirect()->route('dashboard')
            ->with('success', 'Password changed successfully. You can now access the system.');
    }
}
