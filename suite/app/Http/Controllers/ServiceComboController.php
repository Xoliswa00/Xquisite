<?php

namespace App\Http\Controllers;

use App\Models\ServiceCombo;
use App\Modules\Booking\Models\Service;
use App\Modules\POS\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ServiceComboController extends Controller
{
    private function tenantId(): int
    {
        return Auth::user()->tenant_id ?? abort(403, 'No tenant assigned to this account.');
    }

    public function index()
    {
        $combos = ServiceCombo::where('tenant_id', $this->tenantId())
            ->with(['services', 'products'])
            ->latest()
            ->paginate(20);

        return view('service-combos.index', compact('combos'));
    }

    public function create()
    {
        [$services, $products] = $this->pickerOptions();

        return view('service-combos.create', compact('services', 'products'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCombo($request);

        $combo = ServiceCombo::create(array_merge($data, [
            'tenant_id' => $this->tenantId(),
            'is_active' => $request->boolean('is_active', true),
        ]));

        $combo->services()->sync($data['service_ids'] ?? []);
        $combo->products()->sync($data['product_ids'] ?? []);

        return redirect()->route('services.index', ['tab' => 'combos'])->with('success', 'Combo created.');
    }

    public function edit(ServiceCombo $combo)
    {
        abort_unless($combo->tenant_id === $this->tenantId(), 403);
        $combo->load(['services', 'products']);

        [$services, $products] = $this->pickerOptions();

        return view('service-combos.create', compact('combo', 'services', 'products'));
    }

    public function update(Request $request, ServiceCombo $combo)
    {
        abort_unless($combo->tenant_id === $this->tenantId(), 403);

        $data = $this->validateCombo($request);

        $combo->update(array_merge($data, ['is_active' => $request->boolean('is_active', true)]));
        $combo->services()->sync($data['service_ids'] ?? []);
        $combo->products()->sync($data['product_ids'] ?? []);

        return redirect()->route('services.index', ['tab' => 'combos'])->with('success', 'Combo updated.');
    }

    public function destroy(ServiceCombo $combo)
    {
        abort_unless($combo->tenant_id === $this->tenantId(), 403);
        $combo->services()->detach();
        $combo->products()->detach();
        $combo->delete();

        return redirect()->route('services.index', ['tab' => 'combos'])->with('success', 'Combo deleted.');
    }

    public function toggle(ServiceCombo $combo)
    {
        abort_unless($combo->tenant_id === $this->tenantId(), 403);
        $combo->update(['is_active' => !$combo->is_active]);

        return back()->with('success', 'Combo ' . ($combo->is_active ? 'activated' : 'deactivated') . '.');
    }

    /** @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection} */
    private function pickerOptions(): array
    {
        $services = Service::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        // is_available_online, not is_active alone — a combo including a
        // product isn't sellable anywhere if that product isn't actually
        // sold online in the first place (mirrors the storefront's own
        // Product::where('is_available_online', true) gate).
        $products = Product::where('tenant_id', $this->tenantId())
            ->where('is_active', true)
            ->where('is_available_online', true)
            ->where('has_variants', false) // a variant product has no single price/stock to bundle at this level — out of scope for v1
            ->orderBy('name')
            ->get();

        return [$services, $products];
    }

    private function validateCombo(Request $request): array
    {
        $validator = Validator::make($request->all(), [
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string|max:1000',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'is_active'      => 'boolean',
            'service_ids'    => 'nullable|array',
            'service_ids.*'  => 'exists:services,id',
            'product_ids'    => 'nullable|array',
            'product_ids.*'  => 'exists:products,id',
        ]);

        // Neither array is individually required (a combo can now be
        // products-only, services-only, or mixed) — but a "combo" of a
        // single item isn't one, so the total across both must be >= 2.
        $validator->after(function ($validator) use ($request) {
            $total = count($request->input('service_ids', [])) + count($request->input('product_ids', []));
            if ($total < 2) {
                $validator->errors()->add('service_ids', 'Select at least 2 services/products in total.');
            }
        });

        return $validator->validate();
    }
}
