<?php

namespace App\Http\Controllers;

use App\Models\PaymentPlan;
use App\Models\Quote;
use Illuminate\Http\Request;

class PublicQuoteController extends Controller
{
    public function show(Quote $quote, string $token)
    {
        abort_unless(hash_equals($quote->acceptToken(), $token), 403, 'Invalid or expired link.');
        abort_if($quote->isExpired(), 410, 'This quote has expired.');

        return view('public.quotes.show', compact('quote'));
    }

    public function accept(Request $request, Quote $quote, string $token)
    {
        abort_unless(hash_equals($quote->acceptToken(), $token), 403);
        abort_unless(in_array($quote->status, ['sent', 'draft']), 422, 'This quote cannot be accepted.');

        $quote->update(['status' => 'accepted']);

        // Create a payment plan for the deposit + balance
        $plan = PaymentPlan::create([
            'tenant_id'      => $quote->tenant_id,
            'customer_id'    => $quote->customer_id,
            'title'          => $quote->title,
            'total_amount'   => $quote->total,
            'type'           => 'quote_deposit',
            'plannable_type' => Quote::class,
            'plannable_id'   => $quote->id,
        ]);

        $depositAmount = $quote->depositAmount();
        $balance       = $quote->total - $depositAmount;

        $plan->installments()->createMany([
            [
                'installment_number' => 1,
                'label'              => 'Deposit (' . $quote->deposit_percentage . '%)',
                'amount'             => $depositAmount,
                'due_date'           => today()->toDateString(),
                'status'             => 'pending',
            ],
            [
                'installment_number' => 2,
                'label'              => 'Balance',
                'amount'             => $balance,
                'due_date'           => $quote->valid_until?->toDateString() ?? today()->addDays(30)->toDateString(),
                'status'             => 'pending',
            ],
        ]);

        $quote->update(['payment_plan_id' => $plan->id]);

        // Redirect to deposit payment (PayFast or show bank details)
        return redirect()->route('public.quotes.pay', [$quote, $token])
            ->with('success', 'Quote accepted! Please pay the deposit to confirm your booking.');
    }

    public function payDeposit(Quote $quote, string $token)
    {
        abort_unless(hash_equals($quote->acceptToken(), $token), 403);
        $quote->load('paymentPlan.installments');
        $deposit = $quote->paymentPlan?->installments->where('installment_number', 1)->first();

        return view('public.quotes.pay', compact('quote', 'deposit'));
    }

    public function decline(Request $request, Quote $quote, string $token)
    {
        abort_unless(hash_equals($quote->acceptToken(), $token), 403);
        $quote->update(['status' => 'declined']);

        return view('public.quotes.declined', compact('quote'));
    }
}
