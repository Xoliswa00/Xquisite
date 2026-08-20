<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    private function tenantId(): int
    {
        return Auth::user()->tenant_id ?? abort(403, 'No tenant assigned to this account.');
    }

    public function index()
    {
        $promotions = Promotion::where('tenant_id', $this->tenantId())
            ->latest()
            ->paginate(20);

        return view('promotions.index', compact('promotions'));
    }

    public function create()
    {
        return view('promotions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string|max:1000',
            'code'           => 'required|string|max:50|unique:promotions,code',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'applies_to'     => 'required|in:all,services,products',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'max_uses'       => 'nullable|integer|min:1',
            'is_active'      => 'boolean',
        ]);

        Promotion::create(array_merge($data, [
            'tenant_id' => $this->tenantId(),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return redirect()->route('promotions.index')->with('success', 'Promotion created.');
    }

    public function edit(Promotion $promotion)
    {
        abort_unless($promotion->tenant_id === $this->tenantId(), 403);
        return view('promotions.create', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        abort_unless($promotion->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string|max:1000',
            'code'           => 'required|string|max:50|unique:promotions,code,' . $promotion->id,
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'applies_to'     => 'required|in:all,services,products',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'max_uses'       => 'nullable|integer|min:1',
            'is_active'      => 'boolean',
        ]);

        $promotion->update(array_merge($data, ['is_active' => $request->boolean('is_active', true)]));

        return redirect()->route('promotions.index')->with('success', 'Promotion updated.');
    }

    public function destroy(Promotion $promotion)
    {
        abort_unless($promotion->tenant_id === $this->tenantId(), 403);
        $promotion->delete();

        return redirect()->route('promotions.index')->with('success', 'Promotion deleted.');
    }

    public function toggle(Promotion $promotion)
    {
        abort_unless($promotion->tenant_id === $this->tenantId(), 403);
        $promotion->update(['is_active' => !$promotion->is_active]);

        return back()->with('success', 'Promotion ' . ($promotion->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function generateCode(): JsonResponse
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Promotion::where('code', $code)->exists());

        return response()->json(['code' => $code]);
    }
}
