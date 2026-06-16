<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\ServiceCategory;
use App\Models\ServiceCombo;
use App\Modules\Booking\Models\Service;
use Illuminate\Http\Request;

class BookingMenuController extends Controller
{
    public function menu(Request $request)
    {
        $tenantId       = auth()->user()->tenant_id;
        $categoryFilter = $request->query('category');

        $categories = ServiceCategory::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['activeServices' => fn($q) => $q->orderBy('name')])
            ->orderBy('sort_order')
            ->get();

        $uncategorized = Service::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNull('service_category_id')
            ->orderBy('name')
            ->get();

        $combos = ServiceCombo::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('services')
            ->get()
            ->filter(fn($c) => $c->isLive())
            ->values();

        $promotions = Promotion::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->filter(fn($p) => $p->isLive())
            ->values();

        return view('booking.menu', compact('categories', 'uncategorized', 'categoryFilter', 'combos', 'promotions'));
    }
}
