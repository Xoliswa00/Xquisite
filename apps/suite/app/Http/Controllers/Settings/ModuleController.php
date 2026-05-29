<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Services\BillingBridge;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function index()
    {
        $tenant     = auth()->user()->tenant;
        $allModules = config('modules');

        $tenant->load('tenantModules');

        return view('settings.modules', compact('tenant', 'allModules'));
    }

    public function request(Request $request, BillingBridge $billing)
    {
        $request->validate([
            'module' => 'required|string|in:' . implode(',', array_keys(config('modules'))),
        ]);

        $tenant     = auth()->user()->tenant;
        $moduleKey  = $request->module;
        $moduleName = config("modules.{$moduleKey}.name");

        // Create billing subscription and activate module immediately
        $billingSubscriptionId = $billing->createModuleSubscription($tenant, $moduleKey);

        $tenant->activateModule(
            module:                $moduleKey,
            activatedBy:           auth()->id(),
            billingSubscriptionId: $billingSubscriptionId,
        );

        return back()->with('success', "{$moduleName} has been activated on your account.");
    }
}
