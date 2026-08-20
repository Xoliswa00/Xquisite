<?php

namespace App\Http\Controllers\ServiceDelivery;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Modules\ServiceDelivery\Models\ServiceAgreement;
use App\Modules\ServiceDelivery\Models\ServiceAgreementChange;
use App\Modules\ServiceDelivery\Models\SlaPlan;
use App\Services\BillingBridge;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ServiceAgreementController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceAgreement::with(['client', 'slaPlan'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $agreements = $query->paginate(20)->withQueryString();

        return view('service-delivery.agreements.index', compact('agreements'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        $plans   = SlaPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('service-delivery.agreements.create', compact('clients', 'plans'));
    }

    public function store(Request $request, BillingBridge $billing)
    {
        $validated = $request->validate([
            'client_id'          => 'required|exists:clients,id',
            'sla_plan_id'        => 'nullable|exists:sla_plans,id',
            'service_type'       => 'required|in:website_hosting,pos_erp_support,automation_support,reporting_support,general_support,other',
            'plan_name'          => 'required|string|max:150',
            'monthly_fee'        => 'required|numeric|min:0',
            'minutes_allowance'  => 'required|integer|min:0',
            'start_date'         => 'required|date',
            'commitment_months'  => 'required|integer|min:0',
            'billing_day'        => 'required|integer|min:1|max:28',
            'reactivation_fee'   => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        $validated['status'] = 'active';
        $validated['reactivation_fee'] = $validated['reactivation_fee'] ?? 350.00;

        $agreement = ServiceAgreement::create($validated);

        // Generate the first month's charge
        $agreement->generateCurrentPeriodCharge();

        // Sync to billing
        $client = Client::findOrFail($validated['client_id']);
        $billingId = $billing->createServiceAgreementSubscription(
            $client->name, $client->email ?? '', $client->phone,
            $agreement->plan_name, $agreement->monthly_fee, $agreement->start_date->toDateString()
        );

        if ($billingId) {
            $agreement->update(['billing_subscription_id' => $billingId]);
        }

        return redirect()->route('service-agreements.show', $agreement)
            ->with('success', 'Service agreement created and billing subscription activated.');
    }

    public function show(ServiceAgreement $serviceAgreement)
    {
        $serviceAgreement->load([
            'client', 'slaPlan',
            'charges' => fn ($q) => $q->orderByDesc('period'),
            'changes' => fn ($q) => $q->latest(),
        ]);

        return view('service-delivery.agreements.show', compact('serviceAgreement'));
    }

    public function edit(ServiceAgreement $serviceAgreement)
    {
        $clients = Client::orderBy('name')->get();
        $plans   = SlaPlan::where('is_active', true)->orderBy('sort_order')->get();

        return view('service-delivery.agreements.edit', compact('serviceAgreement', 'clients', 'plans'));
    }

    public function update(Request $request, ServiceAgreement $serviceAgreement)
    {
        $validated = $request->validate([
            'plan_name'          => 'required|string|max:150',
            'monthly_fee'        => 'required|numeric|min:0',
            'minutes_allowance'  => 'required|integer|min:0',
            'commitment_months'  => 'required|integer|min:0',
            'billing_day'        => 'required|integer|min:1|max:28',
            'reactivation_fee'   => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ]);

        $serviceAgreement->update($validated);

        return redirect()->route('service-agreements.show', $serviceAgreement)->with('success', 'Agreement updated.');
    }

    public function accept(Request $request, ServiceAgreement $serviceAgreement)
    {
        $validated = $request->validate([
            'accepted_name' => 'required|string|max:150',
            'accepted_at'   => 'required|date',
        ]);

        $serviceAgreement->update($validated);

        return redirect()->route('service-agreements.show', $serviceAgreement)->with('success', 'Acceptance recorded.');
    }

    public function recordCharge(Request $request, ServiceAgreement $serviceAgreement, \App\Modules\ServiceDelivery\Models\ServiceAgreementCharge $charge)
    {
        abort_unless($charge->service_agreement_id === $serviceAgreement->id, 404);

        $validated = $request->validate([
            'amount_paid'    => 'required|numeric|min:0.01',
            'paid_date'      => 'required|date',
            'payment_method' => 'required|in:eft,cash,card,debit_order,other',
            'reference'      => 'nullable|string|max:100',
        ]);

        $amountPaid = (float) $validated['amount_paid'];
        $status = $amountPaid >= $charge->amount_due ? 'paid' : 'partial';

        $charge->update(array_merge($validated, [
            'amount_paid' => $amountPaid,
            'status'      => $status,
        ]));

        if ($status === 'paid' && $serviceAgreement->status === 'suspended') {
            $serviceAgreement->update(['status' => 'active', 'late_stage' => 'current', 'last_reminder_stage_sent' => null]);
        }

        return redirect()->route('service-agreements.show', $serviceAgreement)
            ->with('success', $status === 'paid' ? 'Payment recorded — fully paid.' : 'Partial payment recorded.');
    }

    public function logMinutes(Request $request, ServiceAgreement $serviceAgreement)
    {
        $validated = $request->validate([
            'description'  => 'required|string|max:255',
            'minutes_used' => 'required|integer|min:1',
        ]);

        ServiceAgreementChange::create(array_merge($validated, [
            'service_agreement_id' => $serviceAgreement->id,
            'period'                => ServiceAgreement::currentPeriod(),
            'logged_by'             => auth()->id(),
        ]));

        return redirect()->route('service-agreements.show', $serviceAgreement)->with('success', 'Change logged.');
    }

    public function suspend(ServiceAgreement $serviceAgreement)
    {
        $serviceAgreement->update(['status' => 'suspended', 'suspended_at' => now()]);

        return redirect()->route('service-agreements.show', $serviceAgreement)->with('success', 'Agreement suspended.');
    }

    public function reactivate(ServiceAgreement $serviceAgreement)
    {
        $serviceAgreement->update(['status' => 'active', 'suspended_at' => null, 'late_stage' => 'current', 'last_reminder_stage_sent' => null]);

        return redirect()->route('service-agreements.show', $serviceAgreement)->with('success', 'Agreement reactivated.');
    }

    public function terminate(Request $request, ServiceAgreement $serviceAgreement, BillingBridge $billing)
    {
        $request->validate([
            'termination_reason' => 'nullable|string|max:500',
            'terminated_at'      => 'required|date',
        ]);

        abort_if($serviceAgreement->status === 'terminated', 422, 'Already terminated.');

        $serviceAgreement->update([
            'status'             => 'terminated',
            'terminated_at'      => $request->terminated_at,
            'termination_reason' => $request->termination_reason,
        ]);

        if ($serviceAgreement->billing_subscription_id) {
            $billing->cancelServiceAgreementSubscription($serviceAgreement->billing_subscription_id);
        }

        return redirect()->route('service-agreements.show', $serviceAgreement)->with('success', 'Agreement terminated.');
    }

    public function contractPdf(ServiceAgreement $serviceAgreement)
    {
        $serviceAgreement->load('client');

        $pdf = Pdf::loadView('service-delivery.agreements.contract-pdf', compact('serviceAgreement'));
        $pdf->getDomPDF()->addInfo('Title', "Service Agreement — {$serviceAgreement->client->name}");
        $pdf->getDomPDF()->addInfo('Author', \App\Models\BillingSetting::get('company_name') ?: config('app.name'));

        return $pdf->stream("service-agreement-{$serviceAgreement->id}.pdf");
    }
}
