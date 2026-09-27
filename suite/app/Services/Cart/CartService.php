<?php

namespace App\Services\Cart;

use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private string $key;

    /**
     * Keyed by tenant ID, not slug/subdomain — the same tenant is now
     * reachable via two different route identifiers (see
     * ResolvesShopTenant), and keying by the raw identifier would give a
     * shopper two different carts depending on which URL they used.
     */
    public function __construct(private readonly int $tenantId)
    {
        $this->key = 'cart.' . $tenantId;
    }

    /** [product_id => qty] */
    public function all(): array
    {
        return session($this->key, []);
    }

    public function add(int $productId, int $qty = 1): void
    {
        $cart = $this->all();
        $cart[$productId] = ($cart[$productId] ?? 0) + $qty;
        session([$this->key => $cart]);
    }

    public function update(int $productId, int $qty): void
    {
        $cart = $this->all();
        if ($qty <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $qty;
        }
        session([$this->key => $cart]);
    }

    public function remove(int $productId): void
    {
        $cart = $this->all();
        unset($cart[$productId]);
        session([$this->key => $cart]);
    }

    public function count(): int
    {
        return array_sum($this->all());
    }

    public function isEmpty(): bool
    {
        return empty($this->all());
    }

    public function clear(): void
    {
        session()->forget($this->key);
    }

    /** Returns products with qty and line subtotals attached */
    public function lines(int $tenantId): Collection
    {
        $items = $this->all();
        if (empty($items)) {
            return collect();
        }

        $products = Product::where('tenant_id', $tenantId)
            ->where('is_available_online', true)
            ->where('is_active', true)
            ->whereIn('id', array_keys($items))
            ->get()
            ->keyBy('id');

        return collect($items)->map(function ($qty, $productId) use ($products) {
            $product = $products->get($productId);
            if (!$product) return null;

            return (object) [
                'product'  => $product,
                'qty'      => $qty,
                'subtotal' => $product->price * $qty,
            ];
        })->filter()->values();
    }

    public function subtotal(int $tenantId): float
    {
        return (float) $this->lines($tenantId)->sum('subtotal');
    }
}
