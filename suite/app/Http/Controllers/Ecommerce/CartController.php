<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\Concerns\ResolvesShopTenant;
use App\Models\Promotion;
use App\Models\ServiceCombo;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ResolvesShopTenant;

    public function view(string $tenantSlug)
    {
        $tenant     = $this->activeShopTenant($tenantSlug);
        $cart       = new CartService($tenant->id);
        $lines      = $cart->lines($tenant->id);
        $comboLines = $cart->comboLines($tenant->id);
        // Includes both plain lines and combo lines — see
        // CartService::subtotal()/comboSubtotal(). Passed once here so the
        // view never has to re-derive it from $lines/$comboLines itself.
        $subtotal = $cart->subtotal($tenant->id);

        // Resolved (not just "is a code stored") so the view can tell a
        // live, applied code apart from a stale one the cart is quietly
        // about to drop — see CartService::promotion().
        $promotion = $cart->promotion($tenant->id);
        $discount  = $promotion ? $promotion->discountFor($subtotal) : 0.0;

        return view('shop.cart', compact('tenant', 'cart', 'lines', 'comboLines', 'subtotal', 'promotion', 'discount'));
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
        $cart   = new CartService($tenant->id);

        $request->validate(['code' => 'required|string|max:50']);

        // Same platform-wide rule as the booking funnel
        // (PublicBookingController::checkPromo(): "Promo codes cannot be
        // combined with combo deals") — a combo's price is already
        // discounted, so stacking a further promo on top of it would be a
        // second, unintended discount on the same items.
        if (!empty($cart->allCombos())) {
            return back()->with('cart_error', 'Promo codes cannot be combined with a bundle deal — remove the bundle first.');
        }

        $promotion = Promotion::findUsable($tenant->id, $request->code);

        if (!$promotion) {
            return back()->with('cart_error', 'That promo code is not valid or has expired.');
        }

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

    public function addCombo(Request $request, string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        $request->validate([
            'combo_id' => 'required|integer',
            'qty'      => 'nullable|integer|min:1|max:99',
        ]);

        $combo = ServiceCombo::where('tenant_id', $tenant->id)
            ->where('id', $request->combo_id)
            ->with('products')
            ->firstOrFail();

        if (!$combo->isLive() || $combo->products->isEmpty()) {
            return back()->with('cart_error', 'That bundle is no longer available.');
        }

        $cart = new CartService($tenant->id);

        // A bundle qty of N needs N units of EVERY constituent product in
        // stock, not just the least-stocked one silently capping the
        // whole bundle — short the qty to whatever the tightest product
        // actually allows and say so, same "cap and explain" pattern as
        // add() above for a single product.
        $requestedQty  = max(1, (int) $request->qty);
        $currentQty    = $cart->allCombos()[$combo->id] ?? 0;
        $maxAffordable = $requestedQty;

        foreach ($combo->products as $product) {
            if (!$product->track_stock) {
                continue;
            }
            $available     = max(0, $product->stock_quantity - $currentQty * 1 /* one of each product per bundle unit */);
            $maxAffordable = min($maxAffordable, $available);
        }

        if ($maxAffordable <= 0) {
            return back()->with('cart_error', 'Not enough stock to add another "' . $combo->name . '" bundle.');
        }

        // Combos and promo codes never coexist (see applyPromo() above) —
        // adding a bundle while a code is already applied drops the code
        // rather than blocking the add, since the bundle is the thing the
        // shopper is actively trying to do right now.
        if ($cart->promoCode()) {
            $cart->removePromoCode();
        }

        $cart->addCombo($combo->id, $maxAffordable);

        // No "bundle" suffix appended — a tenant-chosen combo name already
        // containing the word "Bundle" (a real, observed case) would
        // otherwise read as "Home Starter Bundle bundle added to cart."
        return back()->with('cart_success', 'Added "' . $combo->name . '" to your cart.');
    }

    public function removeCombo(Request $request, string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        $request->validate(['combo_id' => 'required|integer']);

        $cart = new CartService($tenant->id);
        $cart->removeCombo((int) $request->combo_id);

        return back()->with('cart_success', 'Bundle removed.');
    }
}
