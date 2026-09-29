<?php

namespace App\Modules\Ecommerce\Actions;

use App\Models\Tenant;
use App\Modules\Ecommerce\Exceptions\InsufficientStockException;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\ProductVariant;
use App\Modules\POS\Services\InventoryService;
use Illuminate\Support\Collection;

/**
 * Re-validates and reserves stock for a set of cart items.
 *
 * MUST be called inside a database transaction (OrderService provides one).
 * Actual stock reservation is delegated to InventoryService::reserve()/
 * reserveVariant(), which row-lock the product/variant and reject oversell.
 * Prices are read fresh from the product/variant, never trusted from the
 * client-side cart.
 *
 * @param  array<string,array{product_id:int,variant_id:?int,qty:int}>  $items  CartService::all()'s shape
 * @return Collection<int,object>  lines: {product, variant, qty, unit_price, subtotal}
 */
class ReserveInventory
{
    public function __construct(private readonly InventoryService $inventory) {}

    public function handle(Tenant $tenant, array $items, string $reference): Collection
    {
        $lines = collect();

        foreach ($items as $item) {
            $qty = (int) ($item['qty'] ?? 0);
            if ($qty < 1) {
                continue;
            }

            $product = Product::where('tenant_id', $tenant->id)
                ->where('id', $item['product_id'])
                ->where('is_active', true)
                ->where('is_available_online', true)
                ->first();

            // Product vanished or was delisted between browsing and checkout.
            if (! $product) {
                throw new InsufficientStockException('One of the items in your cart is no longer available. Please review your cart.');
            }

            $variant = null;
            if (!empty($item['variant_id'])) {
                $variant = ProductVariant::where('tenant_id', $tenant->id)
                    ->where('product_id', $product->id)
                    ->where('id', $item['variant_id'])
                    ->where('is_active', true)
                    ->first();

                // Variant was deactivated/deleted between browsing and checkout.
                if (! $variant) {
                    throw new InsufficientStockException('One of the items in your cart is no longer available. Please review your cart.');
                }
            }

            // Locks the row, revalidates, and decrements (or throws).
            if ($variant) {
                $this->inventory->reserveVariant($variant, $qty, $reference);
            } else {
                $this->inventory->reserve($product, $qty, $reference);
            }

            $unitPrice = $variant ? $variant->effectivePrice() : (float) $product->price;

            $lines->push((object) [
                'product'    => $product,
                'variant'    => $variant,
                'qty'        => $qty,
                'unit_price' => $unitPrice,
                'subtotal'   => $unitPrice * $qty,
            ]);
        }

        if ($lines->isEmpty()) {
            throw new InsufficientStockException('Your cart is empty or its items are no longer available.');
        }

        return $lines;
    }
}
