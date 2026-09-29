<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundingTwentyApplication;
use App\Models\PromoCode;
use App\Models\Tenant;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FoundingTwentyController extends Controller
{
    public function index()
    {
        $applications = FoundingTwentyApplication::latest('score')->latest()->get();

        $stats = [
            'total' => $applications->count(),
            'high' => $applications->where('tier', 'high')->count(),
            'good' => $applications->where('tier', 'good')->count(),
            'selected' => $applications->where('status', 'selected')->count(),
        ];

        return view('admin.founding-twenty.index', compact('applications', 'stats'));
    }

    public function show(FoundingTwentyApplication $foundingTwenty)
    {
        $foundingTwenty->load(['reviewer', 'tenant', 'promoCodeRedemption.promoCode', 'checkins', 'outreachCampaign', 'referredByTenant']);

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        return view('admin.founding-twenty.show', ['application' => $foundingTwenty, 'tenants' => $tenants]);
    }

    public function updateStatus(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewing,selected,waitlisted,rejected,converted',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $updates = [
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ];

        // First time an application is selected, issue its reservation deposit reference.
        if ($request->status === 'selected' && $foundingTwenty->deposit_amount === null) {
            $updates['deposit_amount'] = config('founding_twenty.deposit_amount');
            $updates['deposit_reference'] = 'F20-' . str_pad($foundingTwenty->id, 4, '0', STR_PAD_LEFT);
        }

        $foundingTwenty->update($updates);

        return back()->with('success', "Application marked as {$request->status}.");
    }

    public function confirmDeposit(FoundingTwentyApplication $foundingTwenty)
    {
        abort_unless($foundingTwenty->deposit_submitted_at !== null, 422, 'No proof of payment has been submitted yet.');

        $foundingTwenty->update(['deposit_confirmed_at' => now()]);

        return back()->with('success', 'Deposit confirmed — this business can now be onboarded.');
    }

    public function markDepositRefunded(FoundingTwentyApplication $foundingTwenty)
    {
        abort_unless($foundingTwenty->deposit_confirmed_at !== null, 422, 'Deposit has not been confirmed yet.');

        $foundingTwenty->update(['deposit_refunded_at' => now()]);

        return back()->with('success', 'Deposit marked as refunded.');
    }

    public function linkTenant(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        abort_if($foundingTwenty->tenant_id !== null, 422, 'This application is already linked to a tenant.');

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $foundingTwenty->update($validated);

        return back()->with('success', 'Tenant linked to this application.');
    }

    public function markMilestone(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        abort_if($foundingTwenty->first_value_milestone_at !== null, 422, 'A first-value milestone has already been logged for this application.');

        $validated = $request->validate([
            'first_value_milestone_note' => 'required|string|max:255',
        ]);

        $foundingTwenty->update([
            ...$validated,
            'first_value_milestone_at' => now(),
        ]);

        return back()->with('success', 'First-value milestone logged.');
    }

    public function issueCheckin(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        $request->validate([
            'checkin_type' => 'required|in:30_day,60_day,90_day',
        ]);

        abort_if(
            $foundingTwenty->checkins()->where('checkin_type', $request->checkin_type)->exists(),
            422,
            'A ' . str_replace('_', '-', $request->checkin_type) . ' check-in already exists for this application.'
        );

        $foundingTwenty->checkins()->create(['checkin_type' => $request->checkin_type]);

        return back()->with('success', 'Check-in link issued — copy it from the panel below.');
    }

    public function processReferralReward(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        abort_unless($foundingTwenty->referred_by_tenant_id !== null, 422, 'This application was not referred by anyone.');
        abort_unless($foundingTwenty->tenant_id !== null, 422, 'This application must be linked to a paying tenant first.');
        abort_if($foundingTwenty->referral_reward_processed_at !== null, 422, 'The referral reward has already been processed.');

        $referrer = Tenant::findOrFail($foundingTwenty->referred_by_tenant_id);
        $newTenant = Tenant::findOrFail($foundingTwenty->tenant_id);

        // Both-sides referral: the referrer gets a free month, the new business gets
        // a welcome discount — two canonical codes, reused across every referral.
        $rewardCode = PromoCode::firstOrCreate(
            ['code' => 'REFERRAL-REWARD'],
            ['type' => 'free_months', 'value' => 1, 'source' => 'referral_program', 'is_active' => true,
             'notes' => 'Auto-created — one free month for referring a paying business.']
        );
        $welcomeCode = PromoCode::firstOrCreate(
            ['code' => 'REFERRAL-WELCOME'],
            ['type' => 'percentage', 'value' => 50, 'source' => 'referral_program', 'is_active' => true,
             'notes' => 'Auto-created — 50% off for a new business that joined via referral.']
        );

        $rewardCode->redemptions()->create([
            'tenant_id' => $referrer->id,
            'discount_type' => $rewardCode->type,
            'discount_value' => $rewardCode->value,
            'financial_value' => $referrer->monthlyTotal(),
            'notes' => "Referral reward for referring {$newTenant->name}.",
            'redeemed_by' => $request->user()->id,
            'redeemed_at' => now(),
        ]);
        $rewardCode->increment('times_redeemed');

        $welcomeCode->redemptions()->create([
            'tenant_id' => $newTenant->id,
            'founding_twenty_application_id' => $foundingTwenty->id,
            'discount_type' => $welcomeCode->type,
            'discount_value' => $welcomeCode->value,
            'financial_value' => round($newTenant->monthlyTotal() * 0.5, 2),
            'notes' => "Welcome discount for joining via {$referrer->name}'s referral.",
            'redeemed_by' => $request->user()->id,
            'redeemed_at' => now(),
        ]);
        $welcomeCode->increment('times_redeemed');

        $foundingTwenty->update(['referral_reward_processed_at' => now()]);

        return back()->with('success', "Referral reward processed — {$referrer->name} got a free month, {$newTenant->name} got a welcome discount.");
    }

    public function downloadPop(FoundingTwentyApplication $foundingTwenty)
    {
        abort_unless($foundingTwenty->deposit_pop_path !== null, 404);

        AuditService::log(
            action: 'document.accessed',
            entityType: 'FoundingTwentyApplication',
            entityId: $foundingTwenty->id,
            meta: ['file' => 'deposit_pop', 'deposit_reference' => $foundingTwenty->deposit_reference],
        );

        return Storage::disk('private')->download(
            $foundingTwenty->deposit_pop_path,
            'deposit-pop-' . $foundingTwenty->deposit_reference . '.' . pathinfo($foundingTwenty->deposit_pop_path, PATHINFO_EXTENSION)
        );
    }
}
