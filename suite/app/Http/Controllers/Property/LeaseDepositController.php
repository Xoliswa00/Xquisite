<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Lease;
use App\Modules\Property\Models\LeaseDepositDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaseDepositController extends Controller
{
    public function storeDeduction(Request $request, Lease $lease)
    {
        abort_if($lease->deposit_status === 'refunded', 422, 'Deposit has already been refunded.');

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:0.01',
        ]);

        $lease->depositDeductions()->create($validated);

        return back()->with('success', 'Deduction added.');
    }

    public function destroyDeduction(Lease $lease, LeaseDepositDeduction $deduction)
    {
        abort_unless($deduction->lease_id === $lease->id, 404);
        abort_if($lease->deposit_status === 'refunded', 422, 'Deposit has already been refunded.');

        $deduction->delete();

        return back()->with('success', 'Deduction removed.');
    }

    public function refund(Request $request, Lease $lease)
    {
        $validated = $request->validate([
            'deposit_refund_amount' => 'required|numeric|min:0',
            'deposit_refund_date'   => 'required|date',
            'deposit_refund_method' => 'required|in:eft,cash,card,debit_order,other',
        ]);

        DB::transaction(function () use ($lease, $validated) {
            // Re-fetch and lock so a deduction added in another request between page
            // load and submit can't be bypassed by a stale client-computed amount.
            $lease = Lease::whereKey($lease->id)->lockForUpdate()->firstOrFail();

            abort_if($lease->status !== 'terminated', 422, 'Deposit can only be refunded once the lease is terminated.');
            abort_if($lease->deposit_status === 'refunded', 422, 'Deposit has already been refunded.');

            $validated['deposit_refund_amount'] = min($validated['deposit_refund_amount'], $lease->availableDepositRefund());
            $validated['deposit_status'] = 'refunded';

            $lease->update($validated);
        });

        return back()->with('success', 'Deposit reconciled and refund recorded.');
    }
}
