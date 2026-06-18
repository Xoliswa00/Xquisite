<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BillingQueue;
use App\Models\BillingSetting;
use App\Models\Modules\Core\Models\SystemLog;
use App\Models\PlatformInvoice;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\BillingPopSubmittedNotification;
use App\Services\PlatformBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

        $tenants = Tenant::withCount([
            'platformInvoices as unpaid_count' => function ($q) {
                $q->whereIn('status', ['unpaid', 'overdue']);
            }
        ])->paginate(20);

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

    public function updateBillingInfo(Request $request)
    {
        $tenant = auth()->user()->tenant;
        abort_if(!$tenant, 403);

        $data = $request->validate([
            'address'    => 'nullable|string|max:500',
            'vat_number' => 'nullable|string|max:30',
        ]);

        $tenant->update($data);

        return back()->with('success', 'Billing information updated. It will appear on future invoices.');
    }

    public function uploadPop(Request $request, PlatformInvoice $invoice)
    {
        abort_unless($invoice->tenant_id === auth()->user()->tenant_id, 403);
        abort_unless(in_array($invoice->status, ['unpaid', 'overdue']), 422);

        $request->validate([
            'pop_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $request->file('pop_file')->store(
            'proofs/' . $invoice->tenant_id,
            'private'
        );

        $invoice->update([
            'pop_path'        => $path,
            'pop_uploaded_at' => now(),
        ]);

        User::permission('manage-tenants')->each(function ($admin) use ($invoice) {
            $admin->notify(new BillingPopSubmittedNotification($invoice));
        });

        return back()->with('success', 'Proof of payment submitted. We will confirm your payment within 1–2 business days.');
    }

    public function downloadPop(PlatformInvoice $invoice)
    {
        $user = auth()->user();

        if ($invoice->tenant_id !== $user->tenant_id) {
            SystemLog::create([
                'level'      => 'CRITICAL',
                'message'    => "Unauthorised POP download attempt on invoice {$invoice->invoice_number}",
                'context'    => ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number, 'tenant_id' => $invoice->tenant_id],
                'user_id'    => $user->id,
                'ip_address' => request()->ip(),
                'url'        => request()->fullUrl(),
                'source'     => 'billing',
                'status'     => 'new',
            ]);
            abort(403);
        }

        abort_unless($invoice->hasPop(), 404);

        AuditLog::create([
            'action'      => 'document.accessed',
            'entity_type' => 'PlatformInvoice',
            'entity_id'   => $invoice->id,
            'user_id'     => $user->id,
            'ip_address'  => request()->ip(),
            'url'         => request()->fullUrl(),
            'meta'        => ['file' => 'pop', 'invoice_number' => $invoice->invoice_number],
        ]);

        return Storage::disk('private')->download(
            $invoice->pop_path,
            'proof-of-payment-' . $invoice->invoice_number . '.' . pathinfo($invoice->pop_path, PATHINFO_EXTENSION)
        );
    }

    public function adminDownloadPop(PlatformInvoice $invoice)
    {
        $user = auth()->user();

        if (!$user->isSystemAdmin()) {
            SystemLog::create([
                'level'      => 'CRITICAL',
                'message'    => "Unauthorised admin POP download attempt on invoice {$invoice->invoice_number}",
                'context'    => ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number],
                'user_id'    => $user->id,
                'ip_address' => request()->ip(),
                'url'        => request()->fullUrl(),
                'source'     => 'billing',
                'status'     => 'new',
            ]);
            abort(403);
        }

        abort_unless($invoice->hasPop(), 404);

        AuditLog::create([
            'action'      => 'document.accessed',
            'entity_type' => 'PlatformInvoice',
            'entity_id'   => $invoice->id,
            'user_id'     => $user->id,
            'ip_address'  => request()->ip(),
            'url'         => request()->fullUrl(),
            'meta'        => ['file' => 'pop', 'role' => 'admin', 'invoice_number' => $invoice->invoice_number, 'tenant_id' => $invoice->tenant_id],
        ]);

        return Storage::disk('private')->download(
            $invoice->pop_path,
            'pop-' . $invoice->invoice_number . '.' . pathinfo($invoice->pop_path, PATHINFO_EXTENSION)
        );
    }

    public function downloadPdf(PlatformInvoice $invoice)
    {
        $user = auth()->user();

        if ($invoice->tenant_id !== $user->tenant_id) {
            SystemLog::create([
                'level'      => 'CRITICAL',
                'message'    => "Unauthorised PDF download attempt on invoice {$invoice->invoice_number}",
                'context'    => ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number, 'tenant_id' => $invoice->tenant_id],
                'user_id'    => $user->id,
                'ip_address' => request()->ip(),
                'url'        => request()->fullUrl(),
                'source'     => 'billing',
                'status'     => 'new',
            ]);
            abort(403);
        }

        AuditLog::create([
            'action'      => 'document.accessed',
            'entity_type' => 'PlatformInvoice',
            'entity_id'   => $invoice->id,
            'user_id'     => $user->id,
            'ip_address'  => request()->ip(),
            'url'         => request()->fullUrl(),
            'meta'        => ['file' => 'pdf', 'invoice_number' => $invoice->invoice_number],
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('billing.invoice-pdf', ['invoice' => $invoice->load('tenant')]);
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function adminDownloadPdf(PlatformInvoice $invoice)
    {
        $user = auth()->user();

        if (!$user->isSystemAdmin()) {
            SystemLog::create([
                'level'      => 'CRITICAL',
                'message'    => "Unauthorised admin PDF download attempt on invoice {$invoice->invoice_number}",
                'context'    => ['invoice_id' => $invoice->id, 'invoice_number' => $invoice->invoice_number, 'tenant_id' => $invoice->tenant_id],
                'user_id'    => $user->id,
                'ip_address' => request()->ip(),
                'url'        => request()->fullUrl(),
                'source'     => 'billing',
                'status'     => 'new',
            ]);
            abort(403);
        }

        AuditLog::create([
            'action'      => 'document.accessed',
            'entity_type' => 'PlatformInvoice',
            'entity_id'   => $invoice->id,
            'user_id'     => $user->id,
            'ip_address'  => request()->ip(),
            'url'         => request()->fullUrl(),
            'meta'        => ['file' => 'pdf', 'role' => 'admin', 'invoice_number' => $invoice->invoice_number, 'tenant_id' => $invoice->tenant_id],
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('billing.invoice-pdf', ['invoice' => $invoice->load('tenant')]);
        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function adminGenerateInvoice(Tenant $tenant)
    {
        abort_unless(auth()->user()->isSystemAdmin(), 403);

        $invoice = $this->billing->generateInvoice($tenant);

        return redirect()->route('admin.billing.show', ['tenant' => $tenant->id])->with('success', "Invoice {$invoice->invoice_number} generated.");
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

        $due = $this->billing->tenantsDueForBilling();

        if ($due->isEmpty()) {
            return redirect()->route('admin.billing.index')->with('info', 'No tenants are due for billing this month.');
        }

        foreach ($due as $tenant) {
            \App\Jobs\GenerateTenantInvoiceJob::dispatch($tenant);
        }

        return redirect()->route('admin.billing.index')
            ->with('success', "Queued {$due->count()} invoice generation job(s). They will process in the background.");
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
            'company_name'         => 'nullable|string|max:100',
            'company_address'      => 'nullable|string|max:500',
            'company_email'        => 'nullable|email|max:100',
            'company_phone'        => 'nullable|string|max:30',
            'company_vat'          => 'nullable|string|max:30',
            'bank_name'            => 'nullable|string|max:60',
            'bank_account_name'    => 'nullable|string|max:100',
            'bank_account_number'  => 'nullable|string|max:30',
            'bank_branch_code'     => 'nullable|string|max:20',
            'whatsapp_number'      => 'nullable|string|max:20|regex:/^[0-9]+$/',
            'whatsapp_message'     => 'nullable|string|max:255',
        ]);

        $timing = array_intersect_key($data, array_flip(['grace_period_days', 'invoice_due_days', 'billing_day_of_month']));
        foreach ($timing as $key => $value) {
            BillingSetting::set($key, (string) $value);
        }

        // Normalise WhatsApp number: strip +, spaces, dashes so it's always digits-only
        if (! empty($data['whatsapp_number'])) {
            $data['whatsapp_number'] = preg_replace('/[^0-9]/', '', $data['whatsapp_number']);
        }

        $companyKeys = ['company_name', 'company_address', 'company_email', 'company_phone', 'company_vat',
                        'bank_name', 'bank_account_name', 'bank_account_number', 'bank_branch_code',
                        'whatsapp_number', 'whatsapp_message'];
        foreach ($companyKeys as $key) {
            BillingSetting::set($key, (string) ($data[$key] ?? ''));
        }

        BillingSetting::set('auto_billing_enabled', $request->has('auto_billing_enabled') ? '1' : '0');

        return back()->with('success', 'Billing settings saved.');
    }
}
