<?php

namespace App\Http\Controllers\ServiceDelivery;

use App\Http\Controllers\Controller;
use App\Modules\ServiceDelivery\Models\ServiceAgreement;
use App\Modules\ServiceDelivery\Models\ServiceAgreementCharge;
use Illuminate\Http\Request;

class ServiceAgreementChargeController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceAgreementCharge::with(['serviceAgreement', 'client'])->latest('due_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('period')) {
            $query->where('period', $request->period);
        }

        $charges = $query->paginate(30)->withQueryString();

        return view('service-delivery.charges.index', compact('charges'));
    }

    /** Generate monthly charge records for all active agreements */
    public function generateMonthly()
    {
        $agreements = ServiceAgreement::where('status', 'active')->get();
        $created = 0;

        foreach ($agreements as $agreement) {
            $charge = $agreement->generateCurrentPeriodCharge();
            if ($charge->wasRecentlyCreated) {
                $created++;
            }
        }

        return back()->with('success', "{$created} new charge(s) generated for " . now()->format('F Y') . '.');
    }

    /** Mark overdue — flag any pending charges past due date */
    public function flagOverdue()
    {
        $count = ServiceAgreementCharge::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);

        return back()->with('success', "{$count} charge(s) marked as overdue.");
    }
}
