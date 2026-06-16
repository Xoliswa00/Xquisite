<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use App\Models\PaymentPlanInstallment;
use App\Modules\Booking\Models\Customer;
use App\Notifications\PaymentReceivedNotification;
use Illuminate\Http\Request;

class PaymentPlanController extends Controller
{
    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        return view('payment-plans.create', compact('customers'));
    }

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $plans = PaymentPlan::where('tenant_id', $tenantId)
            ->with(['installments', 'customer'])
            ->latest()
            ->paginate(30);

        $overdueCount = PaymentPlan::where('tenant_id', $tenantId)->overdue()->count();

        return view('payment-plans.index', compact('plans', 'overdueCount'));
    }

    public function show(PaymentPlan $paymentPlan)
    {
        $this->authorise($paymentPlan);
        $paymentPlan->load(['installments', 'customer', 'plannable']);

        return view('payment-plans.show', compact('paymentPlan'));
    }

    public function storePlan(Request $request)
    {
        $data = $request->validate([
            'title'                  => 'required|string|max:150',
            'total_amount'           => 'required|numeric|min:1',
            'deposit_amount'         => 'required|numeric|min:0',
            'remaining_installments' => 'required|integer|min:0|max:24',
            'interval_days'          => 'required|integer|min:1',
            'deposit_due'            => 'required|date|after_or_equal:today',
            'cancellation_fee'       => 'nullable|numeric|min:0',
            'type'                   => 'required|in:layby,event_deposit,quote_deposit,custom',
            'customer_id'            => 'nullable|exists:customers,id',
            'notes'                  => 'nullable|string|max:1000',
            'plannable_type'         => 'nullable|string',
            'plannable_id'           => 'nullable|integer',
        ]);

        $plan = PaymentPlan::create([
            'tenant_id'        => auth()->user()->tenant_id,
            'customer_id'      => $data['customer_id'] ?? null,
            'title'            => $data['title'],
            'total_amount'     => $data['total_amount'],
            'cancellation_fee' => $data['cancellation_fee'] ?? 0,
            'type'             => $data['type'],
            'notes'            => $data['notes'] ?? null,
            'plannable_type'   => $data['plannable_type'] ?? null,
            'plannable_id'     => $data['plannable_id'] ?? null,
        ]);

        $schedule = PaymentPlan::buildSchedule(
            $data['total_amount'],
            $data['deposit_amount'],
            $data['remaining_installments'],
            $data['deposit_due'],
            $data['interval_days']
        );

        foreach ($schedule as $row) {
            $plan->installments()->create($row);
        }

        return redirect()->route('payment-plans.show', $plan)
            ->with('success', 'Payment plan created.');
    }

    public function recordPayment(Request $request, PaymentPlanInstallment $installment)
    {
        $this->authorise($installment->plan);

        $data = $request->validate([
            'payment_method' => 'required|in:cash,eft,card,payfast',
            'reference'      => 'nullable|string|max:100',
        ]);

        $installment->markPaid($data['payment_method'], $data['reference'] ?? null);

        // Notify the tenant owner
        $owner = auth()->user()->tenant->users()->where('role', 'owner')->first();
        if ($owner) {
            $owner->notify(new PaymentReceivedNotification(
                (float) $installment->amount,
                $data['reference'] ?? 'N/A',
                $data['payment_method']
            ));
        }

        return back()->with('success', "{$installment->label} of R" . number_format($installment->amount, 2) . " marked as paid.");
    }

    public function cancel(Request $request, PaymentPlan $paymentPlan)
    {
        $this->authorise($paymentPlan);

        $request->validate(['reason' => 'nullable|string|max:500']);

        $fee = $paymentPlan->cancellation_fee;
        $paymentPlan->update([
            'status' => 'cancelled',
            'notes'  => $paymentPlan->notes . "\nCancelled: " . ($request->reason ?? 'No reason given'),
        ]);

        return redirect()->route('payment-plans.index')
            ->with('success', "Plan cancelled." . ($fee > 0 ? " Cancellation fee of R" . number_format($fee, 2) . " applies." : ''));
    }

    private function authorise(PaymentPlan $plan): void
    {
        abort_unless($plan->tenant_id === auth()->user()->tenant_id, 403);
    }
}
