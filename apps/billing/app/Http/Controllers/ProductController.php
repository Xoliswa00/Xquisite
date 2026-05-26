<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductGroup;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $company = auth()->user()->currentCompany;

        if (!$company) {
            return view('products.index', ['products' => collect(), 'stats' => []]);
        }

        $products = Product::where('company_id', $company->id)
            ->with(['pricing' => fn($q) => $q->where('is_active', true), 'group', 'category'])
            ->latest()
            ->paginate(15);

        $stats = [
            'total_products'    => Product::where('company_id', $company->id)->count(),
            'active_products'   => Product::where('company_id', $company->id)->where('is_active', true)->count(),
            'recurring_products'=> Product::where('company_id', $company->id)->where('billing_type', 'recurring')->count(),
            'new_this_month'    => Product::where('company_id', $company->id)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        return view('products.index', compact('products', 'stats'));
    }

    public function create()
    {
        $groups = ProductGroup::with('categories')->get();
        return view('products.create', compact('groups'));
    }

    public function store(Request $request)
    {
        $company = auth()->user()->currentCompany;
        abort_if(!$company, 403, 'No active company.');

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'product_group_id'    => 'required|exists:product_groups,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'billing_type'        => 'required|in:once_off,recurring',
            'billing_cycle'       => 'nullable|in:monthly,yearly',
            'pricing_type'        => 'required|in:fixed,hourly,range,per_item',
            'price'               => 'nullable|numeric|min:0',
            'min_price'           => 'nullable|numeric|min:0',
            'max_price'           => 'nullable|numeric|min:0',
            'items'               => 'nullable|array',
            'items.*.name'        => 'required_with:items|string',
            'items.*.description' => 'nullable|string',
            'items.*.is_included' => 'nullable|in:on,off,1,0',
            'items.*.price'       => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $company) {
            $product = Product::create([
                'company_id'          => $company->id,
                'name'                => $validated['name'],
                'description'         => $validated['description'] ?? null,
                'product_group_id'    => $validated['product_group_id'],
                'product_category_id' => $validated['product_category_id'] ?? null,
                'billing_type'        => $validated['billing_type'],
                'billing_cycle'       => $validated['billing_cycle'] ?? null,
            ]);

            if (!empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $included = in_array($item['is_included'] ?? 'off', ['on', '1', 1, true], true);
                    $product->items()->create([
                        'name'        => $item['name'],
                        'description' => $item['description'] ?? null,
                        'is_included' => $included,
                        'price'       => $included ? null : ($item['price'] ?? null),
                    ]);
                }
            }

            $product->pricing()->create([
                'pricing_type'   => $validated['pricing_type'],
                'price'          => $validated['price'] ?? null,
                'min_price'      => $validated['min_price'] ?? null,
                'max_price'      => $validated['max_price'] ?? null,
                'vat_rate'       => 15,
                'is_active'      => true,
                'effective_from' => now(),
            ]);
        });

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);
        $product->load(['items', 'pricing', 'group', 'category']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        $groups = ProductGroup::with('categories')->get();
        $product->load(['items', 'pricing']);
        return view('products.edit', compact('product', 'groups'));
    }

    public function update(Request $request, Product $product)
    {
        $this->authorize('update', $product);

        $validated = $request->validate([
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string',
            'product_group_id'    => 'required|exists:product_groups,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'billing_type'        => 'required|in:once_off,recurring',
            'billing_cycle'       => 'nullable|in:monthly,yearly',
        ]);

        $product->update($validated);

        return redirect()->route('products.show', $product)->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }
}
