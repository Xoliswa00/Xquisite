<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quote;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $company = auth()->user()->currentCompany;

        if (!$company) {
            return view('reports.index', ['stats' => [], 'revenueByMonth' => collect(), 'topClients' => collect()]);
        }

        $cid = $company->id;

        $stats = [
            'total_invoiced'  => Invoice::where('company_id', $cid)->sum('total'),
            'total_paid'      => Payment::where('company_id', $cid)->sum('amount'),
            'total_outstanding' => Invoice::where('company_id', $cid)
                ->whereIn('status', ['draft', 'sent'])
                ->sum('total'),
            'total_overdue'   => Invoice::where('company_id', $cid)
                ->where('status', 'overdue')
                ->sum('total'),
            'invoice_count'   => Invoice::where('company_id', $cid)->count(),
            'client_count'    => Client::where('company_id', $cid)->count(),
            'quote_count'     => Quote::where('company_id', $cid)->count(),
        ];

        $revenueByMonth = Payment::where('company_id', $cid)
            ->selectRaw('YEAR(payment_date) as year, MONTH(payment_date) as month, SUM(amount) as total')
            ->groupByRaw('YEAR(payment_date), MONTH(payment_date)')
            ->orderByRaw('YEAR(payment_date) DESC, MONTH(payment_date) DESC')
            ->limit(12)
            ->get();

        $topClients = Client::where('company_id', $cid)
            ->withSum('invoices', 'total')
            ->orderByDesc('invoices_sum_total')
            ->limit(10)
            ->get();

        return view('reports.index', compact('stats', 'revenueByMonth', 'topClients'));
    }

    public function revenue(Request $request)
    {
        $company = auth()->user()->currentCompany;
        abort_if(!$company, 403);

        $year = $request->integer('year', now()->year);

        $monthly = Payment::where('company_id', $company->id)
            ->whereYear('payment_date', $year)
            ->selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $invoiced = Invoice::where('company_id', $company->id)
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, SUM(total) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        return view('reports.revenue', compact('monthly', 'invoiced', 'year'));
    }

    public function outstanding()
    {
        $company = auth()->user()->currentCompany;
        abort_if(!$company, 403);

        $invoices = Invoice::where('company_id', $company->id)
            ->whereIn('status', ['draft', 'sent', 'overdue'])
            ->with('client')
            ->orderBy('due_date')
            ->get();

        return view('reports.outstanding', compact('invoices'));
    }
}
