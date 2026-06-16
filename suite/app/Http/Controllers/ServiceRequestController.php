<?php

namespace App\Http\Controllers;

use App\Models\PlatformService;
use App\Models\TenantServiceOrder;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $services = PlatformService::active()->requestable()->ordered()
            ->get()
            ->groupBy('category');

        $myOrders = TenantServiceOrder::where('tenant_id', auth()->user()->tenant_id)
            ->with('service')
            ->latest()
            ->get();

        return view('settings.services', compact('services', 'myOrders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'platform_service_id' => 'required|exists:platform_services,id',
            'requested_date'      => 'nullable|date|after_or_equal:today',
            'client_notes'        => 'nullable|string|max:1000',
        ]);

        $service = PlatformService::findOrFail($data['platform_service_id']);
        $tenant  = auth()->user()->tenant;

        $existing = TenantServiceOrder::where('tenant_id', $tenant->id)
            ->where('platform_service_id', $service->id)
            ->whereIn('status', ['requested', 'quoted', 'approved', 'in_progress'])
            ->first();

        if ($existing) {
            return back()->with('error', "You already have an active request for {$service->name}.");
        }

        TenantServiceOrder::create([
            'platform_service_id' => $service->id,
            'tenant_id'           => $tenant->id,
            'requested_by'        => auth()->id(),
            'status'              => 'requested',
            'client_notes'        => $data['client_notes'] ?? null,
            'requested_date'      => $data['requested_date'] ?? null,
        ]);

        return back()->with('success', "Your request for {$service->name} has been submitted. We'll be in touch shortly.");
    }
}
