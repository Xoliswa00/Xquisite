<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FoundingTwentyApplication;
use App\Models\Tenant;
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
        $foundingTwenty->load(['reviewer', 'tenant', 'promoCodeRedemption.promoCode']);

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
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $foundingTwenty->update($validated);

        return back()->with('success', 'Tenant linked to this application.');
    }

    public function markMilestone(Request $request, FoundingTwentyApplication $foundingTwenty)
    {
        $validated = $request->validate([
            'first_value_milestone_note' => 'required|string|max:255',
        ]);

        $foundingTwenty->update([
            ...$validated,
            'first_value_milestone_at' => now(),
        ]);

        return back()->with('success', 'First-value milestone logged.');
    }

    public function downloadPop(FoundingTwentyApplication $foundingTwenty)
    {
        abort_unless($foundingTwenty->deposit_pop_path !== null, 404);

        return Storage::disk('private')->download(
            $foundingTwenty->deposit_pop_path,
            'deposit-pop-' . $foundingTwenty->deposit_reference . '.' . pathinfo($foundingTwenty->deposit_pop_path, PATHINFO_EXTENSION)
        );
    }
}
