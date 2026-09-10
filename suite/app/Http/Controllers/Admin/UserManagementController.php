<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateStaffAccount;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    /**
     * Permissions a tenant owner/manager may assign to their own staff.
     *
     * Excludes platform-operator permissions (manage-tenants, view-system-logs,
     * …) so this screen can't escalate a staff account beyond the tenant's own
     * business, and excludes `manage-staff` itself so an owner can't hand an
     * employee the keys to this very screen (self-promotion, resetting peers,
     * granting `manage-staff` onward). `manage-staff` stays carried only by the
     * `manager` role — mirrors PermissionRoleSeeder's role map.
     */
    private const ASSIGNABLE_PERMISSIONS = [
        'manage-products',
        'manage-orders',
        'manage-appointments',
        'manage-customers',
        'manage-properties',
        'view-reports',
    ];
    /**
     * Display a listing of users for the tenant.
     */
    public function index(Request $request): View
    {
        Gate::authorize('manage-staff');

        $showTrashed = $request->boolean('trashed');

        $query = $showTrashed
            ? User::onlyTrashed()->with('roles')->where('tenant_id', auth()->user()->tenant_id)
            : User::with('roles')->where('tenant_id', auth()->user()->tenant_id);

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
     * Plain-language reference: what an Employee vs a Manager can do.
     */
    public function teamGuide(): View
    {
        Gate::authorize('manage-staff');

        return view('admin.users.team-guide');
    }

    /**
     * Show the form for creating a new staff account.
     */
    public function create(): View
    {
        Gate::authorize('manage-staff');

        $permissions = Permission::whereIn('name', self::ASSIGNABLE_PERMISSIONS)
            ->orderBy('name')->get();

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
            'password' => 'nullable|string|min:8|confirmed',
            'permissions' => 'sometimes|array',
            'permissions.*' => ['string', Rule::in(self::ASSIGNABLE_PERMISSIONS)],
        ]);

        $ownerSetPassword = filled($validated['password'] ?? null);
        $plainPassword    = $ownerSetPassword ? $validated['password'] : Str::password(12);

        $staff = $action->execute([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'tenant_id' => auth()->user()->tenant_id,
            'password' => $plainPassword,
        ]);

        $staff->syncRoles([$validated['role']]);

        if (! empty($validated['permissions'])) {
            $staff->syncPermissions($validated['permissions']);
        }

        // The owner created this account and typed the email — they vouch for it.
        // Verifying it here keeps the "no email round-trip needed" promise: a
        // staff member with no working inbox can still sign in with the password
        // handed to them. (`require_password_change` still forces a reset first.)
        $staff->forceFill(['email_verified_at' => now()])->save();

        // Courtesy heads-up when there's a real inbox; the credential itself is
        // shown to the owner on the next screen, not sent in this mail.
        if (! $ownerSetPassword) {
            Mail::to($staff->email)->queue(new \App\Mail\WelcomeStaffEmail($staff));
        }

        $message = $ownerSetPassword
            ? "{$staff->name}'s account is ready. Give them the temporary password below — they'll set their own on first sign-in."
            : "{$staff->name}'s account is ready. Share the temporary password below (also emailed to {$staff->email}) — they'll set their own on first sign-in.";

        return redirect()->route('admin.users.show', $staff)
            ->with('success', $message)
            ->with('temp_password', $plainPassword);
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

        $permissions = Permission::whereIn('name', self::ASSIGNABLE_PERMISSIONS)
            ->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'permissions'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('manage-staff');
        $this->ensureOwnersTenant($user);

        $this->ensureCanManage($user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:manager,employee',
            'is_active' => 'boolean',
            'permissions' => 'sometimes|array',
            'permissions.*' => ['string', Rule::in(self::ASSIGNABLE_PERMISSIONS)],
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
        $this->ensureCanManage($user);

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
        $this->ensureCanManage($user);

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
        $this->ensureCanManage($user);

        $validated = $request->validate([
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $plainPassword = filled($validated['password'] ?? null)
            ? $validated['password']
            : Str::password(12);

        $user->update([
            'require_password_change' => true,
            'password' => Hash::make($plainPassword),
        ]);

        return redirect()->route('admin.users.show', $user)
            ->with('success', "Password reset for {$user->name}. Share the temporary password below — they'll set their own on next sign-in.")
            ->with('temp_password', $plainPassword);
    }

    /**
     * Soft-delete a staff account. Owners cannot be deleted.
     * All their historical data (sales, appointments) is preserved.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('manage-staff');
        $this->ensureOwnersTenant($user);
        $this->ensureCanManage($user);

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

        $this->ensureCanManage($user);

        $user->restore();

        return redirect()->route('admin.users.show', $user)
            ->with('success', "{$user->name}'s account has been restored.");
    }

    /**
     * Ensure the target user belongs to the acting user's tenant.
     */
    private function ensureOwnersTenant(User $user): void
    {
        if ($user->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }

    /**
     * Guard mutating actions on a target account. The owner account is off
     * limits to everyone here, and a manager may not act on another manager —
     * only the owner can — so one manager can't demote, lock out, or delete a
     * peer. (Managers acting on employees is the normal case and stays allowed.)
     */
    private function ensureCanManage(User $user): void
    {
        abort_if($user->isOwner(), 403, 'The owner account cannot be changed here.');

        if ($user->hasRole('manager') && ! auth()->user()->isOwner()) {
            abort(403, 'Only the business owner can manage another manager\'s account.');
        }
    }
}
