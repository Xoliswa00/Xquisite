<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\Concerns\ResolvesShopTenant;
use App\Models\Promotion;
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

        // Resolved (not just "is a code stored") so the view can tell a
        // live, applied code apart from a stale one the cart is quietly
        // about to drop — see CartService::promotion().
        $promotion = $cart->promotion($tenant->id);

        return view('shop.cart', compact('tenant', 'cart', 'lines', 'promotion'));
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

    /**
     * Stores the code for display/preview on the cart page only — this is
     * NOT the authoritative redemption. OrderService::placeOrder()
     * re-validates with Promotion::findUsable() at checkout time regardless
     * of what's in the session, so a code that goes stale (deactivated,
     * expires, hits max_uses) between "applied to cart" and "placed order"
     * can never slip through.
     */
    public function applyPromo(Request $request, string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        $request->validate(['code' => 'required|string|max:50']);

        $promotion = Promotion::findUsable($tenant->id, $request->code);

        if (!$promotion) {
            return back()->with('cart_error', 'That promo code is not valid or has expired.');
        }

        $cart = new CartService($tenant->id);
        $cart->setPromoCode($promotion->code);

        return back()->with('cart_success', 'Promo code applied.');
    }

    public function removePromo(string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        $cart = new CartService($tenant->id);
        $cart->removePromoCode();

        return back()->with('cart_success', 'Promo code removed.');
    }
}
