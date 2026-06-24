<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user   = $request->user();
        $tenant = $user->tenant;

        return view('profile.edit', compact('user', 'tenant'));
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function updateBusiness(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $data = $request->validate([
            'business_name'       => 'required|string|max:100',
            'slug'                => ['required', 'string', 'min:3', 'max:60', 'regex:/^[a-z0-9][a-z0-9-]*[a-z0-9]$/',
                                    Rule::unique('tenants', 'slug')->ignore($tenant->id)],
            'email'               => 'nullable|email|max:100',
            'phone'               => 'nullable|string|max:30',
            'address'             => 'nullable|string|max:500',
            'bank_name'           => 'nullable|string|max:100',
            'bank_account_type'   => 'nullable|in:cheque,savings',
            'bank_account_holder' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_branch_code'    => 'nullable|string|max:20',
        ], [
            'slug.regex' => 'Slug may only contain lowercase letters, numbers, and hyphens (no leading/trailing hyphens).',
        ]);

        $tenant->update([
            'name'                => $data['business_name'],
            'slug'                => $data['slug'],
            'email'               => $data['email'] ?? null,
            'phone'               => $data['phone'] ?? null,
            'address'             => $data['address'] ?? null,
            'bank_name'           => $data['bank_name'] ?? null,
            'bank_account_type'   => $data['bank_account_type'] ?? null,
            'bank_account_holder' => $data['bank_account_holder'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_branch_code'    => $data['bank_branch_code'] ?? null,
        ]);

        return Redirect::route('profile.edit')->with('status', 'business-updated');
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        abort_unless($tenant, 403);

        $request->validate([
            'logo' => 'required|image|mimes:jpeg,jpg,png,webp,svg|max:2048',
        ]);

        // Delete old logo if stored in our storage (not an external URL)
        if ($tenant->logo_url && str_starts_with($tenant->logo_url, '/storage/')) {
            $old = str_replace('/storage/', 'public/', $tenant->logo_url);
            Storage::delete($old);
        }

        $path = $request->file('logo')->store("public/logos/{$tenant->id}");
        $tenant->update(['logo_url' => Storage::url($path)]);

        return Redirect::route('profile.edit')->with('status', 'logo-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
