<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Property\Models\MaintenanceRequest;
use App\Modules\Property\Models\RentPayment;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RenterPortalController extends Controller
{
    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    private function requireRenter(string $slug)
    {
        if (!Auth::guard('renter')->check()) {
            return redirect()->route('rent.login', $slug);
        }
        return null;
    }

    public function portal(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireRenter($slug)) return $r;

        $renter = Auth::guard('renter')->user();
        $renter->load(['leases.property', 'leases.unit', 'leases.rentPayments']);

        $activeLease = $renter->activeLease?->load(['property', 'unit', 'rentPayments' => fn($q) => $q->latest('period')->limit(6)]);
        $openMaintenance = MaintenanceRequest::where('renter_id', $renter->id)->whereIn('status', ['open', 'in_progress'])->count();

        return view('property.portal.dashboard', compact('tenant', 'slug', 'renter', 'activeLease', 'openMaintenance'));
    }

    public function lease(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireRenter($slug)) return $r;

        $renter      = Auth::guard('renter')->user();
        $activeLease = $renter->activeLease?->load(['property', 'unit']);

        return view('property.portal.lease', compact('tenant', 'slug', 'renter', 'activeLease'));
    }

    public function payments(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireRenter($slug)) return $r;

        $renter   = Auth::guard('renter')->user();
        $payments = RentPayment::where('renter_id', $renter->id)
            ->with(['unit.property'])
            ->orderByDesc('period')
            ->paginate(12);

        return view('property.portal.payments', compact('tenant', 'slug', 'renter', 'payments'));
    }

    public function maintenance(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireRenter($slug)) return $r;

        $renter   = Auth::guard('renter')->user();
        $requests = MaintenanceRequest::where('renter_id', $renter->id)
            ->with(['unit'])
            ->latest()
            ->paginate(15);

        return view('property.portal.maintenance', compact('tenant', 'slug', 'renter', 'requests'));
    }

    public function submitMaintenance(string $slug, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireRenter($slug)) return $r;

        $renter      = Auth::guard('renter')->user();
        $activeLease = $renter->activeLease;
        abort_if(!$activeLease, 403, 'You need an active lease to raise a maintenance request.');

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'priority'    => 'required|in:low,medium,high,urgent',
        ]);

        MaintenanceRequest::create([
            'tenant_id'   => $tenant->id,
            'property_id' => $activeLease->property_id,
            'unit_id'     => $activeLease->unit_id,
            'lease_id'    => $activeLease->id,
            'renter_id'   => $renter->id,
            'title'       => $validated['title'],
            'description' => $validated['description'],
            'priority'    => $validated['priority'],
            'status'      => 'open',
        ]);

        return redirect()->route('rent.maintenance', $slug)
            ->with('success', 'Maintenance request submitted. We\'ll be in touch shortly.');
    }
}
