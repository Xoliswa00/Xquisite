<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
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

    public function request(Request $request)
    {
        $request->validate([
            'module' => 'required|string|in:' . implode(',', array_keys(config('modules'))),
        ]);

        $tenant     = auth()->user()->tenant;
        $moduleName = config("modules.{$request->module}.name");

        // In a real billing flow this would create a subscription item.
        // For now it logs the request — admin will activate from the admin panel.
        \Log::info("Module request: tenant={$tenant->id} module={$request->module}");

        return back()->with('success', "Your request to activate {$moduleName} has been received. Our team will be in touch shortly.");
    }
}
