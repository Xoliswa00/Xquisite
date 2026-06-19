<?php

namespace App\Modules\Ecommerce\Actions;

use App\Models\Tenant;
use App\Modules\Ecommerce\Exceptions\InsufficientStockException;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Services\InventoryService;
use Illuminate\Support\Collection;

/**
 * Re-validates and reserves stock for a set of cart items.
 *
 * MUST be called inside a database transaction (OrderService provides one).
 * Actual stock reservation is delegated to InventoryService::reserve(), which
 * row-locks each product and rejects oversell. Prices are read fresh from the
 * product, never trusted from the client-side cart.
 *
 * @param  array<int,int>  $items  [product_id => qty]
 * @return Collection<int,object>  lines: {product, qty, unit_price, subtotal}
 */
class ReserveInventory
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function handle(Tenant $tenant, array $items, string $reference): Collection
    {
        $lines = collect();

        foreach ($items as $productId => $qty) {
            $qty = (int) $qty;
            if ($qty < 1) {
                continue;
            }

            $product = Product::where('tenant_id', $tenant->id)
                ->where('id', $productId)
                ->where('is_active', true)
                ->where('is_available_online', true)
                ->first();

            // Product vanished or was delisted between browsing and checkout.
            if (! $product) {
                throw new InsufficientStockException('One of the items in your cart is no longer available. Please review your cart.');
            }

            // Locks the row, revalidates, and decrements (or throws).
            $this->inventory->reserve($product, $qty, $reference);

            $lines->push((object) [
                'product'    => $product,
                'qty'        => $qty,
                'unit_price' => (float) $product->price,
                'subtotal'   => (float) $product->price * $qty,
            ]);
        }

        if ($lines->isEmpty()) {
            throw new InsufficientStockException('Your cart is empty or its items are no longer available.');
        }

        return $lines;
    }
}
