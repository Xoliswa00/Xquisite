<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function view(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->where('is_active', true)->firstOrFail();
        $cart   = new CartService($tenantSlug);
        $lines  = $cart->lines($tenant->id);

        return view('shop.cart', compact('tenant', 'cart', 'lines'));
    }

    public function add(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->where('is_active', true)->firstOrFail();

        $request->validate([
            'product_id' => 'required|integer',
            'qty'        => 'nullable|integer|min:1|max:99',
        ]);

        $product = Product::where('tenant_id', $tenant->id)
            ->where('id', $request->product_id)
            ->where('is_active', true)
            ->where('is_available_online', true)
            ->firstOrFail();

        $cart = new CartService($tenantSlug);

        // Enforce stock limit for tracked products
        $requestedQty = max(1, (int) $request->qty);
        if ($product->track_stock) {
            $currentQty = $cart->all()[$product->id] ?? 0;
            $requestedQty = min($requestedQty, max(0, $product->stock_quantity - $currentQty));
            if ($requestedQty <= 0) {
                return back()->with('cart_error', 'No more stock available for ' . $product->name . '.');
            }
        }

        $cart->add($product->id, $requestedQty);

        if ($request->expectsJson()) {
            return response()->json(['count' => $cart->count()]);
        }

        return back()->with('cart_success', $product->name . ' added to cart.');
    }

    public function update(Request $request, string $tenantSlug)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'qty'        => 'required|integer|min:0|max:99',
        ]);

        $cart = new CartService($tenantSlug);
        $cart->update((int) $request->product_id, (int) $request->qty);

        return redirect()->route('shop.cart', $tenantSlug);
    }

    public function remove(Request $request, string $tenantSlug)
    {
        $request->validate(['product_id' => 'required|integer']);

        $cart = new CartService($tenantSlug);
        $cart->remove((int) $request->product_id);

        return redirect()->route('shop.cart', $tenantSlug)->with('cart_success', 'Item removed.');
    }
}
