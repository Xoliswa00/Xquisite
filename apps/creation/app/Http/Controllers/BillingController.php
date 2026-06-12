<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\PlatformInvoice;
use App\Services\PlatformBillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(private PlatformBillingService $billing) {}

    // ----------------------------------------------------------------
    // Platform owner: view their own subscription & invoices
    // ----------------------------------------------------------------

    public function index()
    {
        $user    = auth()->user();
        $company = $user->currentCompany;

        if (!$company) {
            abort(404, 'No company found for your account.');
        }

        $invoices = PlatformInvoice::where('company_id', $company->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        $unpaid = PlatformInvoice::where('company_id', $company->id)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->orderBy('due_date')
            ->get();

        return view('billing.index', compact('company', 'invoices', 'unpaid'));
    }

    public function show(PlatformInvoice $invoice)
    {
        $this->authorizeInvoiceAccess($invoice);
        return view('billing.show', compact('invoice'));
    }

    // ----------------------------------------------------------------
    // System admin: manage all platform billing
    // ----------------------------------------------------------------

    public function adminIndex()
    {
        $this->requireSystemAdmin();

        $companies = Company::with(['user', 'platformInvoices' => fn($q) => $q->latest()->limit(1)])
            ->where('is_platform_company', false)
            ->orderBy('name')
            ->paginate(20);

        return view('billing.admin.index', compact('companies'));
    }

    public function adminShow(Company $company)
    {
        $this->requireSystemAdmin();

        $invoices = PlatformInvoice::where('company_id', $company->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('billing.admin.show', compact('company', 'invoices'));
    }

    public function adminGenerateInvoice(Company $company)
    {
        $this->requireSystemAdmin();

        $invoice = $this->billing->generateInvoice($company);

        return redirect()
            ->route('admin.billing.show', $company)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' generated and sent to platform owner.');
    }

    public function adminMarkPaid(Request $request, PlatformInvoice $invoice)
    {
        $this->requireSystemAdmin();

        $validated = $request->validate([
            'payment_method'    => 'required|string|max:100',
            'payment_reference' => 'required|string|max:255',
        ]);

        $this->billing->recordPayment($invoice, $validated['payment_method'], $validated['payment_reference']);

        return redirect()
            ->route('admin.billing.show', $invoice->company_id)
            ->with('success', 'Payment recorded. Company reactivated if suspended.');
    }

    public function adminSuspend(Company $company)
    {
        $this->requireSystemAdmin();

        $company->update([
            'status'       => 'suspended',
            'suspended_at' => now(),
        ]);

        return back()->with('success', 'Company suspended.');
    }

    public function adminReactivate(Company $company)
    {
        $this->requireSystemAdmin();

        $company->update([
            'status'               => 'active',
            'suspended_at'         => null,
            'grace_period_ends_at' => null,
        ]);

        return back()->with('success', 'Company reactivated.');
    }

    // ----------------------------------------------------------------

    private function authorizeInvoiceAccess(PlatformInvoice $invoice): void
    {
        $user    = auth()->user();
        $company = $user->currentCompany;

        if ($user->isSystemAdmin()) return;

        if (!$company || $invoice->company_id !== $company->id) {
            abort(403);
        }
    }

    private function requireSystemAdmin(): void
    {
        if (!auth()->user()->isSystemAdmin()) {
            abort(403, 'System admin access only.');
        }
    }
}
