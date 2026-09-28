<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\Concerns\ResolvesShopTenant;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ResolvesShopTenant;

    public function view(string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);
        $cart   = new CartService($tenant->id);
        $lines  = $cart->lines($tenant->id);

        return view('shop.cart', compact('tenant', 'cart', 'lines'));
    }

    public function add(Request $request, string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'qty'        => 'nullable|integer|min:1|max:99',
        ]);

        $product = Product::where('tenant_id', $tenant->id)
            ->where('id', $request->product_id)
            ->where('is_active', true)
            ->where('is_available_online', true)
            ->firstOrFail();

        $variant = null;
        if ($product->has_variants) {
            // A variant product can't be added without picking one — the
            // storefront always sends variant_id for these, so a missing
            // one here means a tampered/stale request, not a real gap.
            $variant = ProductVariant::where('product_id', $product->id)
                ->where('id', $request->variant_id)
                ->where('is_active', true)
                ->firstOrFail();
        }

        $cart = new CartService($tenant->id);

        // Enforce stock limit for tracked products/variants
        $requestedQty = max(1, (int) $request->qty);
        $tracksStock  = $variant ? $variant->track_stock : $product->track_stock;
        $stockOnHand  = $variant ? $variant->stock_quantity : $product->stock_quantity;

        if ($tracksStock) {
            $lineKey    = $variant ? "v{$variant->id}" : "p{$product->id}";
            $currentQty = $cart->all()[$lineKey]['qty'] ?? 0;
            $requestedQty = min($requestedQty, max(0, $stockOnHand - $currentQty));
            if ($requestedQty <= 0) {
                $label = $variant ? "{$product->name} ({$variant->label})" : $product->name;
                return back()->with('cart_error', 'No more stock available for ' . $label . '.');
            }
        }

        $cart->add($product->id, $variant?->id, $requestedQty);

        if ($request->expectsJson()) {
            return response()->json(['count' => $cart->count()]);
        }

        return back()->with('cart_success', $product->name . ' added to cart.');
    }

    public function update(Request $request, string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
            'qty'        => 'required|integer|min:0|max:99',
        ]);

        $cart = new CartService($tenant->id);
        $cart->update((int) $request->product_id, $request->filled('variant_id') ? (int) $request->variant_id : null, (int) $request->qty);

        return redirect()->to($tenant->shopRoute('cart'));
    }

    public function remove(Request $request, string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
        ]);

        $cart = new CartService($tenant->id);
        $cart->remove((int) $request->product_id, $request->filled('variant_id') ? (int) $request->variant_id : null);

        return redirect()->to($tenant->shopRoute('cart'))->with('cart_success', 'Item removed.');
    }
}
