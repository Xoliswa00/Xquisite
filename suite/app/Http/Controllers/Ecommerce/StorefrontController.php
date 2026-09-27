<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\Concerns\ResolvesShopTenant;
use App\Modules\POS\Models\Product;
use App\Services\Cart\CartService;
use App\Support\TenantManifest;

class StorefrontController extends Controller
{
    use ResolvesShopTenant;

    /** Public/unauthenticated — a browser fetches this before any login. */
    public function manifest(string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        return TenantManifest::response($tenant, $tenant->shopRoute('index'));
    }

    public function index(string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

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

        $cart = new CartService($tenant->id);

        return view('shop.index', compact('tenant', 'products', 'categories', 'cart', 'category', 'search'));
    }

    public function product(string $tenantSlug, int $productId)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

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

        $cart = new CartService($tenant->id);

        return view('shop.product', compact('tenant', 'product', 'related', 'cart'));
    }
}
