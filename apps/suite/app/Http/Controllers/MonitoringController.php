<?php

namespace App\Http\Controllers;

use App\Models\MonitoredInstance;
use App\Models\HealthCheckLog;
use App\Models\InstanceAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Show all monitored instances
     */
    public function index(): View
    {
        $instances = MonitoredInstance::with(['lastHealthCheck', 'activeAlerts'])
            ->orderByDesc('updated_at')
            ->get();

        return view('admin.monitoring.index', compact('instances'));
    }

    /**
     * Show instance detail page
     */
    public function show(MonitoredInstance $instance): View
    {
        $instance->load(['lastHealthCheck']);
        
        $lastCheck = $instance->lastHealthCheck;
        
        $healthLogs = HealthCheckLog::where('monitored_instance_id', $instance->id)
            ->latest()
            ->limit(20)
            ->get();
        
        $alerts = InstanceAlert::where('monitored_instance_id', $instance->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.monitoring.show', compact('instance', 'lastCheck', 'healthLogs', 'alerts'));
    }

    /**
     * Show the create instance form
     */
    public function create(): View
    {
        return view('admin.monitoring.create');
    }

    /**
     * Store a new monitored instance
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'api_token' => 'required|string|min:32|unique:monitored_instances,api_token',
            'tenant_id' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $validated['is_healthy'] = null;
        $validated['uptime_percentage'] = null;

        MonitoredInstance::create($validated);

        return redirect()->route('monitoring.index')
            ->with('success', 'Instance added successfully. Start with health checks.');
    }

    /**
     * Show the edit instance form
     */
    public function edit(MonitoredInstance $instance): View
    {
        return view('admin.monitoring.edit', compact('instance'));
    }

    /**
     * Update a monitored instance
     */
    public function update(Request $request, MonitoredInstance $instance): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'api_token' => 'required|string|min:32|unique:monitored_instances,api_token,' . $instance->id,
            'tenant_id' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $instance->update($validated);

        return redirect()->route('monitoring.show', $instance)
            ->with('success', 'Instance updated successfully.');
    }

    /**
     * Delete a monitored instance
     */
    public function destroy(MonitoredInstance $instance): RedirectResponse
    {
        $instance->delete();

        return redirect()->route('monitoring.index')
            ->with('success', 'Instance deleted successfully.');
    }
}
