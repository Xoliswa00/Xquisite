<?php

namespace App\Http\Controllers;

use App\Models\ServiceCombo;
use App\Models\product;
use Illuminate\Http\Request;

class ServiceComboController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->managedCompanies->first()->id;
    }

    public function index()
    {
        $companyId = $this->companyId();

        $combos = ServiceCombo::where('company_id', $companyId)
            ->with(['services.prices'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total'   => ServiceCombo::where('company_id', $companyId)->count(),
            'active'  => ServiceCombo::where('company_id', $companyId)->where('is_active', true)->count(),
            'live'    => ServiceCombo::where('company_id', $companyId)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
                ->where(fn($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
                ->count(),
            'expired' => ServiceCombo::where('company_id', $companyId)
                ->where('valid_until', '<', now())
                ->count(),
        ];

        return view('service-combos.index', compact('combos', 'stats'));
    }

    public function create()
    {
        $services = product::where('company_id', $this->companyId())
            ->with(['prices' => fn($q) => $q->where('is_active', true)->latest()])
            ->get();

        return view('service-combos.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'is_active'      => 'boolean',
            'service_ids'    => 'required|array|min:2',
            'service_ids.*'  => 'exists:products,id',
        ]);

        $combo = ServiceCombo::create([
            'company_id'     => $this->companyId(),
            'name'           => $validated['name'],
            'description'    => $validated['description'] ?? null,
            'discount_type'  => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'valid_from'     => $validated['valid_from'] ?? null,
            'valid_until'    => $validated['valid_until'] ?? null,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        $combo->services()->sync($validated['service_ids']);

        return redirect()->route('combos.index')->with('success', 'Combo created successfully.');
    }

    public function edit(ServiceCombo $combo)
    {
        $this->authorizeCombo($combo);

        $combo->load('services.prices');

        $services = product::where('company_id', $this->companyId())
            ->with(['prices' => fn($q) => $q->where('is_active', true)->latest()])
            ->get();

        return view('service-combos.create', compact('combo', 'services'));
    }

    public function update(Request $request, ServiceCombo $combo)
    {
        $this->authorizeCombo($combo);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'is_active'      => 'boolean',
            'service_ids'    => 'required|array|min:2',
            'service_ids.*'  => 'exists:products,id',
        ]);

        $combo->update([
            'name'           => $validated['name'],
            'description'    => $validated['description'] ?? null,
            'discount_type'  => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'valid_from'     => $validated['valid_from'] ?? null,
            'valid_until'    => $validated['valid_until'] ?? null,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        $combo->services()->sync($validated['service_ids']);

        return redirect()->route('combos.index')->with('success', 'Combo updated.');
    }

    public function destroy(ServiceCombo $combo)
    {
        $this->authorizeCombo($combo);
        $combo->delete();

        return redirect()->route('combos.index')->with('success', 'Combo deleted.');
    }

    public function toggle(ServiceCombo $combo)
    {
        $this->authorizeCombo($combo);
        $combo->update(['is_active' => ! $combo->is_active]);

        return back()->with('success', 'Combo status updated.');
    }

    private function authorizeCombo(ServiceCombo $combo): void
    {
        abort_if($combo->company_id !== $this->companyId(), 403);
    }
}
