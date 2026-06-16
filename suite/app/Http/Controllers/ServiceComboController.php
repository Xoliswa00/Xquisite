<?php

namespace App\Http\Controllers;

use App\Models\ServiceCombo;
use App\Modules\Booking\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceComboController extends Controller
{
    private function tenantId(): int
    {
        return Auth::user()->tenant_id ?? abort(403, 'No tenant assigned to this account.');
    }

    public function index()
    {
        $combos = ServiceCombo::where('tenant_id', $this->tenantId())
            ->with('services')
            ->latest()
            ->paginate(20);

        return view('service-combos.index', compact('combos'));
    }

    public function create()
    {
        $services = Service::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('service-combos.create', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string|max:1000',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'is_active'      => 'boolean',
            'service_ids'    => 'required|array|min:2',
            'service_ids.*'  => 'exists:services,id',
        ]);

        $combo = ServiceCombo::create(array_merge($data, [
            'tenant_id' => $this->tenantId(),
            'is_active' => $request->boolean('is_active', true),
        ]));

        $combo->services()->sync($data['service_ids']);

        return redirect()->route('services.index', ['tab' => 'combos'])->with('success', 'Service combo created.');
    }

    public function edit(ServiceCombo $combo)
    {
        abort_unless($combo->tenant_id === $this->tenantId(), 403);
        $combo->load('services');

        $services = Service::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('service-combos.create', compact('combo', 'services'));
    }

    public function update(Request $request, ServiceCombo $combo)
    {
        abort_unless($combo->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string|max:1000',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'is_active'      => 'boolean',
            'service_ids'    => 'required|array|min:2',
            'service_ids.*'  => 'exists:services,id',
        ]);

        $combo->update(array_merge($data, ['is_active' => $request->boolean('is_active', true)]));
        $combo->services()->sync($data['service_ids']);

        return redirect()->route('services.index', ['tab' => 'combos'])->with('success', 'Service combo updated.');
    }

    public function destroy(ServiceCombo $combo)
    {
        abort_unless($combo->tenant_id === $this->tenantId(), 403);
        $combo->services()->detach();
        $combo->delete();

        return redirect()->route('services.index', ['tab' => 'combos'])->with('success', 'Service combo deleted.');
    }

    public function toggle(ServiceCombo $combo)
    {
        abort_unless($combo->tenant_id === $this->tenantId(), 403);
        $combo->update(['is_active' => !$combo->is_active]);

        return back()->with('success', 'Combo ' . ($combo->is_active ? 'activated' : 'deactivated') . '.');
    }
}
