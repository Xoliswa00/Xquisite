<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\Property\Models\MaintenanceQuote;
use App\Modules\Property\Models\MaintenanceRequest;
use App\Services\Tenant\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractorPortalController extends Controller
{
    private function resolveTenant(string $slug): Tenant
    {
        $tenant = Tenant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        TenantContext::set($tenant->id);
        return $tenant;
    }

    private function requireContractor(string $slug)
    {
        if (!Auth::guard('contractor')->check()) {
            return redirect()->route('contractor.login', $slug);
        }
        return null;
    }

    /** Jobs this contractor is invited to quote on, whether or not they've won it yet. */
    private function invitedJobsQuery(int $contractorId)
    {
        return MaintenanceRequest::whereHas('invitedContractors', fn ($q) => $q->where('contractors.id', $contractorId));
    }

    public function portal(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireContractor($slug)) return $r;

        $contractor = Auth::guard('contractor')->user();
        $jobs = $this->invitedJobsQuery($contractor->id)
            ->with(['property', 'unit'])
            ->latest()
            ->get();

        $openJobs = $jobs->whereNotIn('status', ['resolved', 'closed'])->count();
        $pendingQuotes = MaintenanceQuote::where('contractor_id', $contractor->id)->where('status', 'pending')->count();
        $awaitingPayment = MaintenanceQuote::where('contractor_id', $contractor->id)->where('status', 'completed')->count();

        return view('property.contractor-portal.dashboard', compact(
            'tenant', 'slug', 'contractor', 'jobs', 'openJobs', 'pendingQuotes', 'awaitingPayment'
        ));
    }

    public function jobs(string $slug)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireContractor($slug)) return $r;

        $contractor = Auth::guard('contractor')->user();
        $jobs = $this->invitedJobsQuery($contractor->id)
            ->with(['property', 'unit', 'quotes' => fn ($q) => $q->where('contractor_id', $contractor->id)])
            ->latest()
            ->paginate(15);

        return view('property.contractor-portal.jobs.index', compact('tenant', 'slug', 'contractor', 'jobs'));
    }

    public function showJob(string $slug, MaintenanceRequest $job)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireContractor($slug)) return $r;

        $contractor = Auth::guard('contractor')->user();
        abort_unless($job->invitedContractors()->where('contractors.id', $contractor->id)->exists(), 404);

        $job->load(['property', 'unit', 'photos']);
        $quote = MaintenanceQuote::where('maintenance_request_id', $job->id)
            ->where('contractor_id', $contractor->id)
            ->first();

        $awardedToOther = $job->contractor_id && $job->contractor_id !== $contractor->id;

        return view('property.contractor-portal.jobs.show', compact('tenant', 'slug', 'contractor', 'job', 'quote', 'awardedToOther'));
    }

    public function submitQuote(string $slug, MaintenanceRequest $job, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireContractor($slug)) return $r;

        $contractor = Auth::guard('contractor')->user();
        abort_unless($job->invitedContractors()->where('contractors.id', $contractor->id)->exists(), 404);
        abort_if($job->contractor_id && $job->contractor_id !== $contractor->id, 422, 'This job has already been awarded to another contractor.');

        $existing = MaintenanceQuote::where('maintenance_request_id', $job->id)
            ->where('contractor_id', $contractor->id)
            ->first();
        abort_if($existing && !in_array($existing->status, ['pending', 'rejected']), 422, 'This job already has an active quote.');

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes'  => 'nullable|string|max:1000',
        ]);

        MaintenanceQuote::updateOrCreate(
            ['maintenance_request_id' => $job->id, 'contractor_id' => $contractor->id],
            [
                'tenant_id'    => $tenant->id,
                'amount'       => $validated['amount'],
                'notes'        => $validated['notes'] ?? null,
                'status'       => 'pending',
                'submitted_at' => now(),
                'decided_at'   => null,
                'decided_by'   => null,
            ]
        );

        return redirect()->route('contractor.jobs.show', [$slug, $job])->with('success', 'Quote submitted.');
    }

    public function markComplete(string $slug, MaintenanceRequest $job)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireContractor($slug)) return $r;

        $contractor = Auth::guard('contractor')->user();
        abort_unless($job->contractor_id === $contractor->id, 404);

        $quote = MaintenanceQuote::where('maintenance_request_id', $job->id)
            ->where('contractor_id', $contractor->id)
            ->first();
        abort_unless($quote && $quote->status === 'approved', 422, 'This job has no approved quote to complete.');

        $quote->update(['status' => 'completed', 'completed_at' => now()]);

        return redirect()->route('contractor.jobs.show', [$slug, $job])->with('success', 'Job marked complete. Awaiting payment.');
    }

    public function storePhoto(string $slug, MaintenanceRequest $job, Request $request)
    {
        $tenant = $this->resolveTenant($slug);
        if ($r = $this->requireContractor($slug)) return $r;

        $contractor = Auth::guard('contractor')->user();
        abort_unless($job->invitedContractors()->where('contractors.id', $contractor->id)->exists(), 404);

        $validated = $request->validate([
            'photos'   => 'required|array|min:1',
            'photos.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        foreach ($request->file('photos') as $photo) {
            $job->photos()->create([
                'tenant_id' => $tenant->id,
                'path'      => $photo->store('maintenance', 'public'),
            ]);
        }

        return back()->with('success', 'Photos added.');
    }
}
