<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateStaffAccount;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users for the tenant.
     */
    public function index(Request $request): View
    {
        Gate::authorize('manage-staff');

        $showTrashed = $request->boolean('trashed');

        $query = $showTrashed
            ? User::onlyTrashed()->where('tenant_id', auth()->user()->tenant_id)
            : User::where('tenant_id', auth()->user()->tenant_id);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $request->role));
        }

        $users        = $query->orderBy('name')->paginate(15)->withQueryString();
        $trashedCount = User::onlyTrashed()->where('tenant_id', auth()->user()->tenant_id)->count();

        return view('admin.users.index', compact('users', 'showTrashed', 'trashedCount'));
    }

    /**
     * Show the form for creating a new staff account.
     */
    public function create(): View
    {
        Gate::authorize('manage-staff');

        $permissions = Permission::orderBy('name')->get();

        return view('admin.users.create', compact('permissions'));
    }

    /**
     * Store a newly created staff account.
     */
    public function store(Request $request, CreateStaffAccount $action): RedirectResponse
    {
        Gate::authorize('manage-staff');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:manager,employee',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $staff = $action->execute([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'tenant_id' => auth()->user()->tenant_id,
            'role' => $validated['role'],
        ]);

        $staff->syncRoles([$validated['role']]);

        if (! empty($validated['permissions'])) {
            $staff->syncPermissions($validated['permissions']);
        }

        // Queue welcome email with temporary password instructions
        Mail::to($staff->email)->queue(
            new \App\Mail\WelcomeStaffEmail($staff)
        );

        event(new Registered($staff));

        return redirect()->route('admin.users.show', $staff)
            ->with('success', "Staff account created. Login credentials sent to {$staff->email}. They must change their password on first login.");
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View
    {
        Gate::authorize('manage-staff');
        $this->ensureOwnersTenant($user);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        Gate::authorize('manage-staff');
        $this->ensureOwnersTenant($user);

        $permissions = Permission::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'permissions'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-staff');
        $this->ensureOwnersTenant($user);

        // Can't edit owner
        if ($user->isOwner()) {
            abort(403, 'Cannot edit owner account.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:manager,employee',
            'is_active' => 'boolean',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active'),
        ]);

        $user->syncRoles([$validated['role']]);

        if (! empty($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        } else {
            $user->syncPermissions([]);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Deactivate a staff account.
     */
    public function deactivate(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-staff');
        $this->ensureOwnersTenant($user);

        if ($user->isOwner()) {
            abort(403, 'Cannot deactivate owner account.');
        }

        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Staff account deactivated.');
    }

    /**
     * Reactivate a staff account.
     */
    public function activate(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-staff');
        $this->ensureOwnersTenant($user);

        $user->update(['is_active' => true]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Staff account reactivated.');
    }

    /**
     * Reset a staff member's password.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-staff');
        $this->ensureOwnersTenant($user);

        if ($user->isOwner()) {
            abort(403, 'Cannot reset owner password.');
        }

        $user->update([
            'require_password_change' => true,
            'password' => Hash::make(\Illuminate\Support\Str::password(12)),
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', 'Password reset. Staff member must change it on next login.');
    }

    /**
     * Soft-delete a staff account. Owners cannot be deleted.
     * All their historical data (sales, appointments) is preserved.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('manage-staff');
        $this->ensureOwnersTenant($user);

        abort_if($user->isOwner(), 403, 'Cannot delete an owner account.');

        $user->delete(); // soft delete — data preserved

        return redirect()->route('admin.users.index')
            ->with('success', "{$user->name}'s account has been deactivated.");
    }

    /**
     * Restore a soft-deleted staff account.
     */
    public function restore(User $user): RedirectResponse
    {
        Gate::authorize('manage-staff');

        $user = User::withTrashed()->where('id', $user->id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();

        $user->restore();

        return redirect()->route('admin.users.show', $user)
            ->with('success', "{$user->name}'s account has been restored.");
    }

    /**
     * Ensure user belongs to owner's tenant.
     */
    private function ensureOwnersTenant(User $user): void
    {
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }
}
