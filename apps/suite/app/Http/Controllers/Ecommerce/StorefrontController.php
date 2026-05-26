<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use App\Services\Cart\CartService;

class StorefrontController extends Controller
{
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->where('is_active', true)->firstOrFail();

        $query = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('is_available_online', true);

        $category = request('category');
        $search   = request('search');

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products   = $query->orderBy('category')->orderBy('name')->paginate(16)->withQueryString();
        $categories = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('is_available_online', true)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $cart = new CartService($tenantSlug);

        return view('shop.index', compact('tenant', 'products', 'categories', 'cart', 'category', 'search'));
    }

    public function product(string $tenantSlug, int $productId)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->where('is_active', true)->firstOrFail();

        $product = Product::where('tenant_id', $tenant->id)
            ->where('id', $productId)
            ->where('is_active', true)
            ->where('is_available_online', true)
            ->firstOrFail();

        // Related: same category, up to 4
        $related = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('is_available_online', true)
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        $cart = new CartService($tenantSlug);

        return view('shop.product', compact('tenant', 'product', 'related', 'cart'));
    }
}
