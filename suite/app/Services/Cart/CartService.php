<?php

namespace App\Services\Cart;

use App\Models\Tenant;
use App\Modules\POS\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private string $key;

    public function __construct(private readonly string $tenantSlug)
    {
        $this->key = 'cart.' . $tenantSlug;
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
