<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\Concerns\ResolvesShopTenant;
use App\Models\ServiceCombo;
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
        $sort     = request('sort', 'category');

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        match ($sort) {
            'price_asc'  => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest'     => $query->orderBy('created_at', 'desc'),
            default      => $query->orderBy('category')->orderBy('name'),
        };

        // withSum avoids an N+1 stock lookup per card for variant products —
        // the index only ever needs "is anything in stock", not per-variant
        // detail. The activeVariants eager load (id/attributes only, not a
        // full select *) is for swatchColors() on the card — without it,
        // Product::swatchColors() would fire one query per has_variants
        // product on the page.
        $products = $query
            ->withSum(['activeVariants as variant_stock_sum' => fn ($q) => $q->where('track_stock', true)], 'stock_quantity')
            ->with(['activeVariants' => fn ($q) => $q->select('id', 'product_id', 'attributes', 'is_active')])
            ->paginate(16)->withQueryString();
        $categories = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('is_available_online', true)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $cart = new CartService($tenant->id);

        // Small teaser only (top 3) — the shop index is a product grid, not
        // a combo showcase; see combos() for the full listing.
        $featuredCombos = $this->liveProductCombos($tenant->id)->take(3);

        return view('shop.index', compact('tenant', 'products', 'categories', 'cart', 'category', 'search', 'sort', 'featuredCombos'));
    }

    public function combos(string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);
        $combos = $this->liveProductCombos($tenant->id);
        $cart   = new CartService($tenant->id);

        return view('shop.combos', compact('tenant', 'combos', 'cart'));
    }

    /**
     * Live combos that include at least one product, re-checking every
     * constituent product/variant-free product is still actually sellable
     * right now — mirrors PublicBookingController::index()'s "only combos
     * whose every service is bookable right now" re-check, since a product
     * can be deactivated or pulled from online sale after being added to a
     * combo. A combo with services too still shows here (the shop just
     * won't let those services be added to a cart — see
     * shop/combos.blade.php), matching the booking side's own symmetric
     * choice to still show a combo with product items in it.
     */
    private function liveProductCombos(int $tenantId)
    {
        return ServiceCombo::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with(['products', 'services'])
            ->get()
            ->filter(fn (ServiceCombo $combo) =>
                $combo->isLive()
                && $combo->products->isNotEmpty()
                && $combo->products->every(fn (Product $p) => $p->is_active && $p->is_available_online)
            )
            ->values();
    }

    public function product(string $tenantSlug, int $productId)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        $product = Product::where('tenant_id', $tenant->id)
            ->where('id', $productId)
            ->where('is_active', true)
            ->where('is_available_online', true)
            ->with(['activeVariants' => fn ($q) => $q->orderBy('id')])
            ->firstOrFail();

        // Related: same category, up to 4
        $related = Product::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->where('is_available_online', true)
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->withSum(['activeVariants as variant_stock_sum' => fn ($q) => $q->where('track_stock', true)], 'stock_quantity')
            ->limit(4)
            ->get();

        $cart = new CartService($tenant->id);

        return view('shop.product', compact('tenant', 'product', 'related', 'cart'));
    }
}
