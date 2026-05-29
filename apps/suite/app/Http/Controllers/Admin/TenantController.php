<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use App\Services\BillingBridge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::withCount('users')
            ->with('activeModules')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('slug', 'like', "%{$request->search}%");
            });
        }

        $tenants   = $query->paginate(20)->withQueryString();
        $allModules = config('modules');

        return view('admin.tenants.index', compact('tenants', 'allModules'));
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['users', 'tenantModules']);
        $allModules = config('modules');

        return view('admin.tenants.show', compact('tenant', 'allModules'));
    }

    public function create()
    {
        $allModules = config('modules');
        return view('admin.tenants.create', compact('allModules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:tenants,email',
            'phone'         => 'nullable|string|max:30',
            'industry'      => 'nullable|string|max:100',
            'owner_name'    => 'required|string|max:255',
            'owner_email'   => 'required|email|unique:users,email',
            'owner_password'=> 'required|string|min:8',
            'modules'       => 'nullable|array',
            'modules.*'     => 'string|in:' . implode(',', array_keys(config('modules'))),
            'trial_days'    => 'nullable|integer|min:0|max:365',
        ]);

        $slug   = $this->uniqueSlug($request->name);
        $tenant = Tenant::create([
            'name'          => $request->name,
            'slug'          => $slug,
            'subdomain'     => $request->input('subdomain') ?: $slug,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'industry'      => $request->industry,
            'plan'          => 'custom',
            'is_active'     => true,
            'trial_ends_at' => $request->trial_days ? now()->addDays((int) $request->trial_days) : null,
        ]);

        $owner = User::create([
            'name'      => $request->owner_name,
            'email'     => $request->owner_email,
            'password'  => Hash::make($request->owner_password),
            'tenant_id' => $tenant->id,
            'role'      => 'owner',
        ]);

        $billing = app(BillingBridge::class);
        foreach ($request->input('modules', []) as $module) {
            $billingId = $billing->createModuleSubscription($tenant, $module);
            $tenant->activateModule($module, auth()->id(), null, $billingId);
        }

        return redirect()->route('admin.tenants.show', $tenant)
            ->with('success', "Tenant '{$tenant->name}' created with " . count($request->input('modules', [])) . ' module(s).');
    }

    public function toggleModule(Request $request, Tenant $tenant, BillingBridge $billing)
    {
        $request->validate([
            'module'         => 'required|string|in:' . implode(',', array_keys(config('modules'))),
            'active'         => 'required|boolean',
            'price_override' => 'nullable|numeric|min:0',
        ]);

        $moduleKey = $request->module;
        $name      = config("modules.{$moduleKey}.name");

        if ($request->boolean('active')) {
            $billingId = $billing->createModuleSubscription($tenant, $moduleKey);

            $tenant->activateModule(
                module:                $moduleKey,
                activatedBy:           auth()->id(),
                priceOverride:         $request->filled('price_override') ? (float) $request->price_override : null,
                billingSubscriptionId: $billingId,
            );

            return back()->with('success', "{$name} activated for {$tenant->name}.");
        } else {
            // Cancel billing subscription if one exists
            $tenantModule = $tenant->tenantModules()->where('module', $moduleKey)->first();
            if ($tenantModule?->billing_subscription_id) {
                $billing->cancelModuleSubscription($tenantModule->billing_subscription_id);
            }

            $tenant->deactivateModule($moduleKey);
            return back()->with('success', "{$name} deactivated for {$tenant->name}.");
        }
    }

    public function updateSubdomain(Request $request, Tenant $tenant)
    {
        $request->validate([
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9\-]+$/',
                "unique:tenants,subdomain,{$tenant->id}",
            ],
        ]);

        $tenant->update(['subdomain' => $request->subdomain]);

        return back()->with('success', "Subdomain updated to {$request->subdomain}." . config('app.domain', 'xquisite.co.za'));
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->update(['is_active' => false]);

        return redirect()->route('admin.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' has been deactivated.");
    }

    private function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $base = $slug;
        $i    = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
