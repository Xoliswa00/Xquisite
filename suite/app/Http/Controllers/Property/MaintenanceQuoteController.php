<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Modules\Property\Models\MaintenanceQuote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceQuoteController extends Controller
{
    /** Approving a quote awards the job to that contractor and auto-rejects any other quotes still pending. */
    public function approve(MaintenanceQuote $quote)
    {
        abort_unless($quote->status === 'pending', 422, 'Only a pending quote can be approved.');

        DB::transaction(function () use ($quote) {
            $quote->update([
                'status'     => 'approved',
                'decided_at' => now(),
                'decided_by' => Auth::id(),
            ]);

            $quote->maintenanceRequest->update(['contractor_id' => $quote->contractor_id]);

            $otherPending = MaintenanceQuote::where('maintenance_request_id', $quote->maintenance_request_id)
                ->where('id', '!=', $quote->id)
                ->where('status', 'pending')
                ->get();

            foreach ($otherPending as $other) {
                $other->update([
                    'status'     => 'rejected',
                    'decided_at' => now(),
                    'decided_by' => Auth::id(),
                    'notes'      => trim(($other->notes ? $other->notes . ' — ' : '') . 'Automatically rejected: another contractor\'s quote was approved for this job.'),
                ]);
            }
        });

        return back()->with('success', 'Quote approved. Contractor assigned to this job.');
    }

    public function reject(Request $request, MaintenanceQuote $quote)
    {
        abort_unless($quote->status === 'pending', 422, 'Only a pending quote can be rejected.');

        $request->validate(['notes' => 'nullable|string|max:500']);

        $quote->update([
            'status'     => 'rejected',
            'decided_at' => now(),
            'decided_by' => Auth::id(),
            'notes'      => $request->filled('notes') ? $request->input('notes') : $quote->notes,
        ]);

        return back()->with('success', 'Quote rejected.');
    }

    public function markPaid(Request $request, MaintenanceQuote $quote)
    {
        abort_unless($quote->status === 'completed', 422, 'Only a completed job can be marked as paid.');

        $request->validate(['payment_reference' => 'nullable|string|max:255']);

        $quote->update([
            'status'             => 'paid',
            'paid_at'            => now(),
            'payment_reference'  => $request->input('payment_reference'),
        ]);

        return back()->with('success', 'Marked as paid.');
    }
}
