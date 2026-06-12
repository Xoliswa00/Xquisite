<?php

namespace App\Http\Controllers;

use App\Models\product;
use App\Http\Requests\StoreproductRequest;
use App\Http\Requests\UpdateproductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\product_group;
use App\Models\product_category;
use App\Models\ServiceCategory;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
{
    $user = auth()->user();

    // Determine companies the user can access
    if ($user->is_platform_owner) {
        $companyIds = $user->managedCompanies()->pluck('id');
    } else {
        $companyIds = $user->currentCompany
            ? collect([$user->currentCompany->id])
            : collect();
    }

    // PRODUCTS with ACTIVE PRICES
    $products = Product::whereIn('company_id', $companyIds)
        ->with([
            'pricing' => function ($q) {
                $q->where('is_active', true);
            }
        ])
        ->latest()
        ->paginate(10);

    // DASHBOARD STATS
    $stats = [
        'total_products' => Product::whereIn('company_id', $companyIds)->count(),

        'active_products' => Product::whereIn('company_id', $companyIds)
            ->where('is_active', true) // assuming you have this column
            ->count(),

        'recurring_products' => Product::whereIn('company_id', $companyIds)
            ->where('billing_type', 'recurring')
            ->count(),

        'products_with_price' => Product::whereIn('company_id', $companyIds)
            ->whereHas('pricing', function ($q) {
                $q->where('is_active', true);
            })
            ->count(),

        'new_this_month' => Product::whereIn('company_id', $companyIds)
            ->whereMonth('created_at', now()->month)
            ->count(),
    ];

    return view('products.index', compact(
        'products',
        'stats'
    ));
}
public function create()
    {
        $groups = product_group::with('categories')->get();

        $serviceCategories = ServiceCategory::where('company_id', auth()->user()->managedCompanies->first()->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('products.create', compact('groups', 'serviceCategories'));
    }   

public function store(Request $request)
{
    $company = auth()->user()->managedCompanies->first();

    $validated = $request->validate([
        'name' => 'required|string',
        'category' => 'nullable|string',
        'description' => 'nullable|string',
        'product_group_id' => 'required|exists:product_groups,id',
        'product_category_id' => 'required|exists:product_categories,id',


        'billing_type' => 'required|in:once_off,recurring',
        'billing_cycle' => 'nullable|in:monthly,yearly',

        // pricing
        'pricing_type' => 'required|in:fixed,hourly,range,custom,per_item',
        'price' => 'nullable|numeric',

        'min_price' => 'nullable|numeric',
        'max_price' => 'nullable|numeric',

        // items
        'items' => 'nullable|array',
        'items.*.name' => 'required|string',
        'items.*.description'=>'required|string',

        'items.*.is_included'    => 'string|in:on,off',
        'items.*.price'          => 'nullable|numeric',
        'service_category_id'   => 'nullable|exists:service_categories,id',
        'duration_minutes'       => 'nullable|integer|min:1|max:480',
    ]);

    DB::transaction(function () use ($validated) {
            $company = auth()->user()->managedCompanies->first();


        // 1. Create product
        $product = Product::create([
            'company_id'          => $company->id,
            'name'                => $validated['name'],
            'product_group_id'    => $validated['product_group_id'] ?? null,
            'product_category_id' => $validated['product_category_id'] ?? null,
            'service_category_id' => $validated['service_category_id'] ?? null,
            'duration_minutes'    => $validated['duration_minutes'] ?? null,
            'description'         => $validated['description'] ?? null,
            'billing_type'        => $validated['billing_type'],
            'billing_cycle'       => $validated['billing_cycle'] ?? null,
        ]);

        // 2. Create items
        if (!empty($validated['items'])) {
            foreach ($validated['items'] as $item) {
                $product->items()->create([
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,

                    'is_included' => $item['is_included'] ?? true,
                    'price' => $item['is_included'] ? null : ($item['price'] ?? null),
                ]);
            }
        }

        // 3. Create pricing
        $product->pricing()->create([
            'pricing_type' => $validated['pricing_type'],
            'price' => $validated['price'] ?? null,
            'min_price' => $validated['min_price'] ?? null,
            'max_price' => $validated['max_price'] ?? null,
            'vat_rate' => 15,
            'is_active' => true,
            'effective_from' => now(),
        ]);

    });

    return redirect()->route('products.index')
        ->with('success', 'Product created');
}

    public function update(Request $request, Product $product)
    {
        $product->update($request->only(['name','description','price','billing_type','vat_rate']));
        return response()->json($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return response()->noContent();
    }
}
