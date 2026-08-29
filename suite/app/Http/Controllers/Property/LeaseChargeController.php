<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\Lease;
use App\Modules\Property\Models\LeaseCharge;
use Illuminate\Http\Request;

class LeaseChargeController extends Controller
{
    public function store(Request $request, Lease $lease)
    {
        $validated = $request->validate([
            'type'                 => 'required|in:water,electricity,sewerage,levy,late_fee,discount,deposit,other',
            'description'          => 'required|string|max:255',
            'period'               => 'nullable|string|max:7',
            // Required unless a meter start/end + rate are given for water/electricity —
            // in that case the amount is computed below and this is ignored either way.
            'amount_excl'          => 'nullable|numeric|min:0',
            'vat_rate'             => 'nullable|numeric|min:0|max:100',
            'due_date'             => 'nullable|date',
            'meter_reading_start'  => 'nullable|numeric|min:0',
            'meter_reading_end'    => 'nullable|numeric|min:0|gte:meter_reading_start',
            'rate_per_unit'        => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string',
        ]);

        $validated['vat_rate'] = $validated['vat_rate'] ?? 0;

        $isMetered = in_array($validated['type'], ['water', 'electricity'])
            && isset($validated['meter_reading_start'], $validated['meter_reading_end'], $validated['rate_per_unit']);

        if ($isMetered) {
            // Meter readings + a rate are authoritative — computed here rather than trusting
            // whatever the amount field held, so the two can never silently disagree.
            $consumption = $validated['meter_reading_end'] - $validated['meter_reading_start'];
            $validated['amount_excl'] = round($consumption * $validated['rate_per_unit'], 2);
        } elseif (!isset($validated['amount_excl'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'amount_excl' => 'Enter an amount, or provide both meter readings and a rate.',
            ]);
        }

        if ($validated['type'] === 'discount') {
            $validated['amount_excl'] = -$validated['amount_excl'];
        }

        $validated['vat_amount'] = round($validated['amount_excl'] * $validated['vat_rate'] / 100, 2);
        $validated['amount_incl'] = $validated['amount_excl'] + $validated['vat_amount'];

        // Credits (discounts) reduce the balance immediately — there's no one to
        // collect a "payment" from, so they settle on creation rather than sitting
        // pending like a charge owed to the landlord.
        if ($validated['type'] === 'discount') {
            $validated['amount_paid'] = $validated['amount_incl'];
            $validated['status'] = 'paid';
        } else {
            $validated['status'] = 'pending';
        }

        $lease->charges()->create($validated);

        return back()->with('success', 'Charge added.');
    }

    public function show(LeaseCharge $charge)
    {
        $charge->load(['lease.property', 'lease.unit', 'lease.renter']);
        return view('property.charges.show', compact('charge'));
    }

    public function record(Request $request, LeaseCharge $charge)
    {
        $validated = $request->validate([
            'amount_paid'    => 'required|numeric|min:0.01',
            'paid_date'      => 'required|date',
            'payment_method' => 'required|in:eft,cash,card,debit_order,other',
            'reference'      => 'nullable|string|max:100',
        ]);

        $amountPaid = (float) $validated['amount_paid'];
        $status = $amountPaid >= $charge->amount_incl ? 'paid' : 'partial';

        $charge->update(array_merge($validated, [
            'amount_paid' => $amountPaid,
            'status'      => $status,
        ]));

        return redirect()->route('lease-charges.show', $charge)
            ->with('success', $status === 'paid' ? 'Payment recorded — fully paid.' : 'Partial payment recorded.');
    }

    public function destroy(Lease $lease, LeaseCharge $charge)
    {
        abort_unless($charge->lease_id === $lease->id, 404);
        abort_if($charge->amount_paid > 0, 422, 'Cannot remove a charge that already has payments recorded against it.');

        $charge->delete();

        return back()->with('success', 'Charge removed.');
    }
}
