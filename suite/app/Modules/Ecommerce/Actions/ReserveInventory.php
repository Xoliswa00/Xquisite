<?php

namespace App\Modules\Ecommerce\Actions;

use App\Models\ServiceCombo;
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

        // Emptiness is checked once, by the caller, after merging with
        // handleCombos()'s lines too — a cart with only a bundle in it
        // and no plain items is entirely valid and must not throw here.
        return $lines;
    }

    /**
     * Same job as handle() above, for combo (bundle) cart lines — one
     * ProductVariant-free product per constituent, reserved for real stock
     * via the same InventoryService::reserve() row-lock. A combo's price
     * is distributed across its constituent products (proportional to each
     * product's own full price share of the bundle) rather than one
     * OrderItem per bundle: the existing order-detail view, invoice/PDF
     * rendering, and stock-decrement code all already work per-product,
     * so this needs no "this row is secretly a bundle" branch anywhere
     * else in the codebase. The last product absorbs the rounding
     * remainder so the pro-rated lines always sum to EXACTLY
     * combo_price * qty, never a stray cent off from what the shopper was
     * quoted on the cart/checkout page.
     *
     * @param  array<int,int>  $comboItems  [comboId => qty] — CartService::allCombos()'s shape
     * @return Collection<int,object>  lines: {product, combo, qty, unit_price, subtotal} — same shape as handle(), plus 'combo'
     */
    public function handleCombos(Tenant $tenant, array $comboItems, string $reference): Collection
    {
        $lines = collect();

        foreach ($comboItems as $comboId => $qty) {
            $qty = (int) $qty;
            if ($qty < 1) {
                continue;
            }

            $combo = ServiceCombo::where('tenant_id', $tenant->id)
                ->where('id', $comboId)
                ->with('products')
                ->first();

            if (!$combo || !$combo->isLive() || $combo->products->isEmpty()) {
                throw new InsufficientStockException('A bundle in your cart is no longer available. Please review your cart.');
            }

            // ->values(): guarantees sequential 0-based keys regardless of
            // how the relation happened to key the collection, since the
            // last-item check below ($index === count()-1) depends on it.
            $products      = $combo->products->values();
            $comboUnitTotal = $combo->combo_price;                 // per one bundle
            $constituentSum = (float) $products->sum('price');     // full-price sum of one of each constituent
            $allocated      = 0.0;

            foreach ($products as $index => $product) {
                if (!$product->is_active || !$product->is_available_online) {
                    throw new InsufficientStockException('A product in the "' . $combo->name . '" bundle is no longer available. Please review your cart.');
                }

                // One unit of this product per bundle unit purchased.
                $this->inventory->reserve($product, $qty, $reference);

                $isLast = $index === $products->count() - 1;
                if ($isLast) {
                    $unitPrice = round($comboUnitTotal - $allocated, 2);
                } else {
                    $share     = $constituentSum > 0 ? ((float) $product->price / $constituentSum) : 0;
                    $unitPrice = round($comboUnitTotal * $share, 2);
                    $allocated += $unitPrice;
                }

                $lines->push((object) [
                    'product'    => $product,
                    'variant'    => null,
                    'combo'      => $combo,
                    'qty'        => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal'   => $unitPrice * $qty,
                ]);
            }
        }

        return $lines;
    }
}
