<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanModule;
use App\Models\PlatformModule;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::ordered()->with('planModules')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create()
    {
        $availableModules = PlatformModule::active()->orWhere('status', 'beta')->ordered()->get();

        return view('admin.plans.create', compact('availableModules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'key'           => 'required|string|alpha_dash|unique:plans,key',
            'name'          => 'required|string|max:80',
            'tagline'       => 'nullable|string|max:150',
            'description'   => 'nullable|string|max:500',
            'price_monthly' => 'required|numeric|min:0',
            'price_annual'  => 'nullable|numeric|min:0',
            'is_active'     => 'boolean',
            'is_featured'   => 'boolean',
            'sort_order'    => 'nullable|integer|min:0',
            'modules'       => 'nullable|array',
            'modules.*'     => 'string',
        ]);

        $modules = $data['modules'] ?? [];
        unset($data['modules']);

        $data['is_active']   = $request->boolean('is_active', true);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order']  = $data['sort_order'] ?? 0;

        $plan = Plan::create($data);

        foreach ($modules as $moduleKey) {
            PlanModule::create(['plan_id' => $plan->id, 'module_key' => $moduleKey]);
        }

        return redirect()->route('admin.plans.index')
            ->with('success', "{$plan->name} plan created.");
    }

    public function edit(Plan $plan)
    {
        $availableModules = PlatformModule::active()->orWhere('status', 'beta')->ordered()->get();
        $selectedModules  = $plan->moduleKeys();

        return view('admin.plans.edit', compact('plan', 'availableModules', 'selectedModules'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:80',
            'tagline'       => 'nullable|string|max:150',
            'description'   => 'nullable|string|max:500',
            'price_monthly' => 'required|numeric|min:0',
            'price_annual'  => 'nullable|numeric|min:0',
            'is_active'     => 'boolean',
            'is_featured'   => 'boolean',
            'sort_order'    => 'nullable|integer|min:0',
            'modules'       => 'nullable|array',
            'modules.*'     => 'string',
        ]);

        $modules = $data['modules'] ?? [];
        unset($data['modules']);

        $data['is_active']   = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order']  = $data['sort_order'] ?? $plan->sort_order;

        $plan->update($data);

        PlanModule::where('plan_id', $plan->id)->delete();

        foreach ($modules as $moduleKey) {
            PlanModule::create(['plan_id' => $plan->id, 'module_key' => $moduleKey]);
        }

        return redirect()->route('admin.plans.index')
            ->with('success', "{$plan->name} updated.");
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', "{$plan->name} removed.");
    }
}
