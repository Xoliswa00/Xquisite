<?php

namespace App\Http\Controllers;

use App\Models\ServiceCategory;
use App\Models\product;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function menu(Request $request)
    {
        $companyId = auth()->user()->managedCompanies->first()->id;
        $company   = auth()->user()->managedCompanies->first();

        $categories = ServiceCategory::where('company_id', $companyId)
            ->where('is_active', true)
            ->with(['activeServices' => function ($q) {
                $q->with(['prices' => fn($p) => $p->where('is_active', true)->latest()])
                  ->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Services not assigned to any category
        $uncategorized = product::where('company_id', $companyId)
            ->whereNull('service_category_id')
            ->where('is_active', true)
            ->with(['prices' => fn($q) => $q->where('is_active', true)->latest()])
            ->orderBy('name')
            ->get();

        $activeFilter = $request->query('category');

        return view('booking.menu', compact('categories', 'uncategorized', 'company', 'activeFilter'));
    }
}
