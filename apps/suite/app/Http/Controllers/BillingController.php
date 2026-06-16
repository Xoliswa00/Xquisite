<?php

namespace App\Http\Controllers;

use App\Models\BillingQueue;
use App\Models\BillingSetting;
use App\Models\PlatformInvoice;
use App\Models\Tenant;
use App\Services\PlatformBillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function __construct(private PlatformBillingService $billing) {}

    public function index()
    {
        $tenant   = auth()->user()->tenant;
        $invoices = PlatformInvoice::where('tenant_id', $tenant->id)->latest()->paginate(10);
        $unpaid   = PlatformInvoice::where('tenant_id', $tenant->id)->whereIn('status', ['unpaid', 'overdue'])->get();

        $gracePercent = 0;
        if ($tenant->grace_period_ends_at) {
            $total   = 5;
            $left    = $tenant->graceDaysLeft();
            $gracePercent = max(0, min(100, round((($total - $left) / $total) * 100)));
        }

        return view('billing.index', compact('tenant', 'invoices', 'unpaid', 'gracePercent'));
    }

    public function show(PlatformInvoice $invoice)
    {
        abort_unless($invoice->tenant_id === auth()->user()->tenant_id, 403);
        return view('billing.show', compact('invoice'));
    }

    public function adminIndex()
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);

        $tenants = Tenant::withCount(['platformInvoices as unpaid_count' => function ($q) {
            $q->whereIn('status', ['unpaid', 'overdue']);
        }])->with('platformInvoices')->paginate(20);

        $stats = [
            'total'      => Tenant::count(),
            'grace'      => Tenant::whereNotNull('grace_period_ends_at')->whereNull('suspended_at')->count(),
            'suspended'  => Tenant::whereNotNull('suspended_at')->count(),
            'unpaid_rev' => PlatformInvoice::whereIn('status', ['unpaid', 'overdue'])->sum('amount'),
        ];

        $dueTenants = $this->billing->tenantsDueForBilling();
        $queueCount = BillingQueue::pendingCount();

        return view('billing.admin.index', compact('tenants', 'stats', 'dueTenants', 'queueCount'));
    }

    public function adminShow(Tenant $tenant)
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);
        $invoices = $tenant->platformInvoices()->latest()->paginate(20);

        return view('billing.admin.show', compact('tenant', 'invoices'));
    }

    public function adminGenerateInvoice(Tenant $tenant)
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);

        $invoice = $this->billing->generateInvoice($tenant);

        return redirect()->route('admin.billing.show', ['company' => $tenant->id])->with('success', "Invoice {$invoice->invoice_number} generated.");
    }

    public function adminMarkPaid(Request $request, PlatformInvoice $invoice)
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);

        $data = $request->validate([
            'payment_method'    => 'required|string|max:50',
            'payment_reference' => 'required|string|max:100',
        ]);

        $this->billing->recordPayment($invoice, $data['payment_method'], $data['payment_reference']);

        return back()->with('success', 'Payment recorded. Account reactivated if suspended.');
    }

    public function adminSuspend(Tenant $tenant)
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);

        $tenant->update([
            'suspended_at'        => now(),
            'grace_period_ends_at'=> null,
        ]);

        return back()->with('success', "{$tenant->name} has been suspended.");
    }

    public function adminReactivate(Tenant $tenant)
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);

        $tenant->update([
            'suspended_at'        => null,
            'grace_period_ends_at'=> null,
        ]);

        return back()->with('success', "{$tenant->name} has been reactivated.");
    }

    public function adminBatchGenerate()
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);

        $due       = $this->billing->tenantsDueForBilling();
        $generated = 0;
        $queued    = 0;

        foreach ($due as $tenant) {
            try {
                $this->billing->generateInvoice($tenant);
                $generated++;
            } catch (\Throwable $e) {
                BillingQueue::create([
                    'tenant_id'    => $tenant->id,
                    'operation'    => 'generate_invoice',
                    'status'       => 'pending',
                    'max_attempts' => 5,
                    'last_error'   => $e->getMessage(),
                ]);
                $queued++;
            }
        }

        if ($generated === 0 && $queued === 0) {
            return redirect()->route('admin.billing.index')->with('info', 'No tenants are due for billing this month.');
        }

        $parts = array_filter([
            $generated > 0 ? "{$generated} invoice(s) generated" : null,
            $queued > 0    ? "{$queued} queued for retry"         : null,
        ]);

        return redirect()->route('admin.billing.index')->with('success', implode(', ', $parts) . '.');
    }

    public function adminSettings()
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);

        $settings = BillingSetting::getSettings();
        return view('billing.admin.settings', compact('settings'));
    }

    public function adminSettingsSave(Request $request)
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);

        $data = $request->validate([
            'grace_period_days'    => 'required|integer|min:1|max:30',
            'invoice_due_days'     => 'required|integer|min:1|max:60',
            'billing_day_of_month' => 'required|integer|min:1|max:28',
        ]);

        foreach ($data as $key => $value) {
            BillingSetting::set($key, (string) $value);
        }

        // Checkbox is absent from POST when unchecked
        BillingSetting::set('auto_billing_enabled', $request->has('auto_billing_enabled') ? '1' : '0');

        return back()->with('success', 'Billing settings saved.');
    }
}
