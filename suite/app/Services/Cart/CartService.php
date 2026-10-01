<?php

namespace App\Services\Cart;

use App\Models\Promotion;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    private string $key;
    private string $promoKey;

    /**
     * Keyed by tenant ID, not slug/subdomain — the same tenant is now
     * reachable via two different route identifiers (see
     * ResolvesShopTenant), and keying by the raw identifier would give a
     * shopper two different carts depending on which URL they used.
     */
    public function __construct(private readonly int $tenantId)
    {
        $this->key = 'cart.' . $tenantId;

        // A SEPARATE top-level key, not "{$this->key}.promo" — session()
        // treats a dotted string as Arr::set() nested-path notation, so
        // session(["cart.{$tenantId}.promo" => $code]) doesn't write a
        // flat sibling key, it writes INTO the same array that cart items
        // live in at "cart.{$tenantId}", landing a 'promo' => string entry
        // alongside the p{id}/v{id} line-item keys. lines() then iterates
        // it as a line item and crashes on $item['product_id'] against a
        // string. Caught in real-browser verification, not by any test —
        // every existing test sets up the session via ['cart.N' => [...]]
        // directly and never exercises promo + cart items together through
        // this actual key-construction path.
        $this->promoKey = 'cart_promo.' . $tenantId;
    }

    /**
     * [lineKey => ['product_id' => int, 'variant_id' => ?int, 'qty' => int]]
     *
     * lineKey distinguishes "Widget, no variant" from "T-Shirt, Size M, Red"
     * from "T-Shirt, Size L, Red" — a bare product_id can't do that once a
     * product has variants, since the same product_id would collide across
     * every size/color combination a shopper picks.
     */
    public function all(): array
    {
        return session($this->key, []);
    }

    public function add(int $productId, ?int $variantId, int $qty = 1): void
    {
        $cart = $this->all();
        $key  = $this->lineKey($productId, $variantId);
        $existing = $cart[$key]['qty'] ?? 0;
        $cart[$key] = ['product_id' => $productId, 'variant_id' => $variantId, 'qty' => $existing + $qty];
        session([$this->key => $cart]);
    }

    public function update(int $productId, ?int $variantId, int $qty): void
    {
        $cart = $this->all();
        $key  = $this->lineKey($productId, $variantId);

        if ($qty <= 0) {
            unset($cart[$key]);
        } else {
            $cart[$key] = ['product_id' => $productId, 'variant_id' => $variantId, 'qty' => $qty];
        }
        session([$this->key => $cart]);
    }

    public function remove(int $productId, ?int $variantId): void
    {
        $cart = $this->all();
        unset($cart[$this->lineKey($productId, $variantId)]);
        session([$this->key => $cart]);
    }

    public function count(): int
    {
        return collect($this->all())->sum('qty');
    }

    public function isEmpty(): bool
    {
        return empty($this->all());
    }

    public function clear(): void
    {
        session()->forget($this->key);
        $this->removePromoCode();
    }

    /**
     * The promo code itself, stored separately from the cart items so it
     * survives independently of what's in the cart. Validity (does it
     * exist, is it live, does it apply to products) is NOT this class's
     * job — a Promotion lookup needs the DB and tenant scoping, which
     * belongs in the controller/OrderService. This is just session storage.
     */
    public function promoCode(): ?string
    {
        return session($this->promoKey);
    }

    public function setPromoCode(string $code): void
    {
        session([$this->promoKey => strtoupper(trim($code))]);
    }

    public function removePromoCode(): void
    {
        session()->forget($this->promoKey);
    }

    private function lineKey(int $productId, ?int $variantId): string
    {
        return $variantId ? "v{$variantId}" : "p{$productId}";
    }

    /** Returns products (+ variant, if any) with qty and line subtotals attached */
    public function lines(int $tenantId): Collection
    {
        $items = $this->all();
        if (empty($items)) {
            return collect();
        }

        $productIds = collect($items)->pluck('product_id')->unique()->values();
        $variantIds = collect($items)->pluck('variant_id')->filter()->unique()->values();

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_available_online', true)
            ->where('is_active', true)
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $variants = $variantIds->isEmpty() ? collect() : ProductVariant::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        return collect($items)->map(function ($item) use ($products, $variants) {
            $product = $products->get($item['product_id']);
            if (!$product) return null;

            // Variant existed when added but was since deactivated/deleted —
            // drop the line rather than silently sell whatever it falls back to.
            $variant = null;
            if ($item['variant_id']) {
                $variant = $variants->get($item['variant_id']);
                if (!$variant) return null;
            }

            $unitPrice = $variant ? $variant->effectivePrice() : (float) $product->price;

            return (object) [
                'product'    => $product,
                'variant'    => $variant,
                'qty'        => $item['qty'],
                'unit_price' => $unitPrice,
                'subtotal'   => $unitPrice * $item['qty'],
            ];
        })->filter()->values();
    }

    public function subtotal(int $tenantId): float
    {
        return (float) $this->lines($tenantId)->sum('subtotal');
    }

    /**
     * Re-resolves the stored code against the DB on every call rather than
     * trusting the session — a code applied an hour ago may since have been
     * deactivated or hit its max_uses. Returns null (not just "no code
     * set") for a code that's gone stale, so callers never need a separate
     * staleness check.
     */
    public function promotion(int $tenantId): ?Promotion
    {
        $code = $this->promoCode();

        return $code ? Promotion::findUsable($tenantId, $code) : null;
    }

    public function discount(int $tenantId): float
    {
        $promotion = $this->promotion($tenantId);

        return $promotion ? $promotion->discountFor($this->subtotal($tenantId)) : 0.0;
    }

    public function total(int $tenantId): float
    {
        return $this->subtotal($tenantId) - $this->discount($tenantId);
    }
}
