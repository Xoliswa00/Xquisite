<?php

namespace App\Http\Controllers;

use App\Modules\Booking\Models\Appointment;
use App\Modules\Booking\Models\Customer;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\Sale;
use App\Modules\POS\Models\SaleItem;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index()
    {
        // ── Revenue ──────────────────────────────────────────────
        $todayRevenue     = Sale::whereDate('paid_at', today())->sum('total');
        $weekRevenue      = Sale::whereBetween('paid_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total');
        $monthRevenue     = Sale::whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->sum('total');
        $lastMonthRevenue = Sale::whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)->sum('total');

        $monthGrowth = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : null;

        // ── Sales counts ─────────────────────────────────────────
        $todaySales  = Sale::whereDate('paid_at', today())->count();
        $monthSales  = Sale::whereMonth('paid_at', now()->month)->whereYear('paid_at', now()->year)->count();
        $avgSaleValue = $monthSales > 0 ? round($monthRevenue / $monthSales, 2) : 0;

        // ── Revenue last 14 days (chart data) ────────────────────
        $revenueChart = collect();
        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $revenueChart->push([
                'date'    => $date,
                'label'   => now()->subDays($i)->format('d M'),
                'revenue' => (float) Sale::whereDate('paid_at', $date)->sum('total'),
                'count'   => Sale::whereDate('paid_at', $date)->count(),
            ]);
        }

        // ── Payment method breakdown ──────────────────────────────
        $paymentBreakdown = Sale::whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->select('payment_method', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get();

        // ── Bookings ──────────────────────────────────────────────
        $todayBookings  = Appointment::today()->count();
        $monthBookings  = Appointment::whereMonth('scheduled_at', now()->month)
            ->whereYear('scheduled_at', now()->year)->count();
        $completedMonth = Appointment::whereMonth('scheduled_at', now()->month)
            ->whereYear('scheduled_at', now()->year)
            ->where('status', 'completed')->count();
        $completionRate = $monthBookings > 0 ? round(($completedMonth / $monthBookings) * 100) : 0;

        // ── Bookings by status (this month) ──────────────────────
        $bookingsByStatus = Appointment::whereMonth('scheduled_at', now()->month)
            ->whereYear('scheduled_at', now()->year)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // ── Top services (this month by revenue) ─────────────────
        $topServices = SaleItem::where('item_type', 'service')
            ->whereHas('sale', fn($q) => $q
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year))
            ->select('name', DB::raw('SUM(subtotal) as revenue'), DB::raw('COUNT(*) as sold'))
            ->groupBy('name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // ── Top products (this month by revenue) ─────────────────
        $topProducts = SaleItem::where('item_type', 'product')
            ->whereHas('sale', fn($q) => $q
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year))
            ->select('name', DB::raw('SUM(subtotal) as revenue'), DB::raw('COUNT(*) as sold'))
            ->groupBy('name')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        // ── Inventory snapshot ────────────────────────────────────
        $outOfStock    = Product::where('track_stock', true)->where('stock_quantity', '<=', 0)->count();
        $reorderNeeded = Product::where('track_stock', true)
            ->where('reorder_level', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            ->count();
        $totalProducts = Product::where('is_active', true)->count();

        // ── Customers ────────────────────────────────────────────
        $totalCustomers  = Customer::count();
        $newThisMonth    = Customer::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();

        // ── Recent sales ─────────────────────────────────────────
        $recentSales = Sale::with('customer')
            ->orderByDesc('paid_at')
            ->limit(8)
            ->get();

        return view('analytics.index', compact(
            'todayRevenue', 'weekRevenue', 'monthRevenue', 'lastMonthRevenue', 'monthGrowth',
            'todaySales', 'monthSales', 'avgSaleValue',
            'revenueChart',
            'paymentBreakdown',
            'todayBookings', 'monthBookings', 'completedMonth', 'completionRate',
            'bookingsByStatus',
            'topServices', 'topProducts',
            'outOfStock', 'reorderNeeded', 'totalProducts',
            'totalCustomers', 'newThisMonth',
            'recentSales'
        ));
    }
}
