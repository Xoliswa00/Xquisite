<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundingTwentyApplication;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromoCodeController extends Controller
{
    public function index()
    {
        $codes = PromoCode::withCount('redemptions')
            ->withSum('redemptions', 'financial_value')
            ->latest()
            ->get();

        $stats = [
            'total_codes' => $codes->count(),
            'active_codes' => $codes->where('is_active', true)->count(),
            'total_redemptions' => $codes->sum('redemptions_count'),
            'total_value_given' => $codes->sum('redemptions_sum_financial_value'),
        ];

        return view('admin.promo-codes.index', compact('codes', 'stats'));
    }

    public function create()
    {
        return view('admin.promo-codes.create');
    }

    public function store(Request $request)
    {
        // Normalize before validating — otherwise 'abc' passes unique:code against an
        // existing 'ABC' and only collides once uppercased for insert.
        $request->merge(['code' => strtoupper((string) ($request->input('code') ?: Str::random(8)))]);

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promo_codes,code',
            'type' => 'required|in:free_months,percentage,fixed_amount',
            'value' => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($request) {
                if ($request->input('type') === 'percentage' && $value > 100) {
                    $fail('Percentage discounts cannot exceed 100.');
                }
            }],
            'max_redemptions' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $code = PromoCode::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.promo-codes.show', $code)->with('success', "Promo code {$code->code} created.");
    }

    public function show(PromoCode $promoCode)
    {
        $promoCode->load(['redemptions' => fn ($q) => $q->latest('redeemed_at'), 'redemptions.tenant', 'redemptions.foundingTwentyApplication', 'redemptions.redeemer', 'creator']);

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        $foundingTwentyApplications = FoundingTwentyApplication::whereIn('status', ['selected', 'converted'])
            ->orderBy('business_name')
            ->get(['id', 'business_name']);

        return view('admin.promo-codes.show', compact('promoCode', 'tenants', 'foundingTwentyApplications'));
    }

    public function redeem(Request $request, PromoCode $promoCode)
    {
        abort_unless($promoCode->isRedeemable(), 422, 'This promo code can no longer be redeemed.');

        $validated = $request->validate([
            'tenant_id' => 'nullable|required_without:founding_twenty_application_id|exists:tenants,id',
            'founding_twenty_application_id' => [
                'nullable',
                'required_without:tenant_id',
                Rule::exists('founding_twenty_applications', 'id')->whereIn('status', ['selected', 'converted']),
            ],
            'financial_value' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (!empty($validated['founding_twenty_application_id'])) {
            abort_if(
                PromoCodeRedemption::where('founding_twenty_application_id', $validated['founding_twenty_application_id'])->exists(),
                422,
                'This application already has a promo code redemption recorded.'
            );
        }

        $promoCode->redemptions()->create([
            ...$validated,
            'discount_type' => $promoCode->type,
            'discount_value' => $promoCode->value,
            'redeemed_by' => $request->user()->id,
            'redeemed_at' => now(),
        ]);

        $promoCode->increment('times_redeemed');

        return back()->with('success', 'Redemption recorded.');
    }

    public function deactivate(PromoCode $promoCode)
    {
        $promoCode->update(['is_active' => false]);

        return back()->with('success', "Promo code {$promoCode->code} deactivated.");
    }
}
