<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PromotionController extends Controller
{
    private function companyId(): int
    {
        return auth()->user()->managedCompanies->first()->id;
    }

    public function index()
    {
        $companyId = $this->companyId();

        $promotions = Promotion::where('company_id', $companyId)->latest()->paginate(15);

        $stats = [
            'total'     => Promotion::where('company_id', $companyId)->count(),
            'live'      => Promotion::where('company_id', $companyId)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', now()))
                ->where(fn($q) => $q->whereNull('valid_until')->orWhere('valid_until', '>=', now()))
                ->count(),
            'scheduled' => Promotion::where('company_id', $companyId)
                ->where('is_active', true)
                ->where('valid_from', '>', now())
                ->count(),
            'expired'   => Promotion::where('company_id', $companyId)
                ->where('valid_until', '<', now())
                ->count(),
        ];

        return view('promotions.index', compact('promotions', 'stats'));
    }

    public function create()
    {
        return view('promotions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'code'           => 'nullable|string|max:50',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'applies_to'     => 'required|in:all,products,combos',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'max_uses'       => 'nullable|integer|min:1',
            'is_active'      => 'boolean',
        ]);

        Promotion::create([
            'company_id'     => $this->companyId(),
            'name'           => $validated['name'],
            'description'    => $validated['description'] ?? null,
            'code'           => $validated['code'] ? strtoupper($validated['code']) : null,
            'discount_type'  => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'applies_to'     => $validated['applies_to'],
            'valid_from'     => $validated['valid_from'] ?? null,
            'valid_until'    => $validated['valid_until'] ?? null,
            'max_uses'       => $validated['max_uses'] ?? null,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()->route('promotions.index')->with('success', 'Promotion created successfully.');
    }

    public function edit(Promotion $promotion)
    {
        $this->authorizePromotion($promotion);

        return view('promotions.create', compact('promotion'));
    }

    public function update(Request $request, Promotion $promotion)
    {
        $this->authorizePromotion($promotion);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'code'           => 'nullable|string|max:50',
            'discount_type'  => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'applies_to'     => 'required|in:all,products,combos',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
            'max_uses'       => 'nullable|integer|min:1',
            'is_active'      => 'boolean',
        ]);

        $promotion->update([
            'name'           => $validated['name'],
            'description'    => $validated['description'] ?? null,
            'code'           => $validated['code'] ? strtoupper($validated['code']) : null,
            'discount_type'  => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'applies_to'     => $validated['applies_to'],
            'valid_from'     => $validated['valid_from'] ?? null,
            'valid_until'    => $validated['valid_until'] ?? null,
            'max_uses'       => $validated['max_uses'] ?? null,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()->route('promotions.index')->with('success', 'Promotion updated.');
    }

    public function destroy(Promotion $promotion)
    {
        $this->authorizePromotion($promotion);
        $promotion->delete();

        return redirect()->route('promotions.index')->with('success', 'Promotion deleted.');
    }

    public function toggle(Promotion $promotion)
    {
        $this->authorizePromotion($promotion);
        $promotion->update(['is_active' => ! $promotion->is_active]);

        return back()->with('success', 'Promotion status updated.');
    }

    public function generateCode()
    {
        return response()->json(['code' => strtoupper(Str::random(8))]);
    }

    private function authorizePromotion(Promotion $promotion): void
    {
        abort_if($promotion->company_id !== $this->companyId(), 403);
    }
}
