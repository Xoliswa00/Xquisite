<?php

namespace App\Modules\POS\Services;

use App\Modules\Ecommerce\Exceptions\InsufficientStockException;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for stock mutations.
 *
 * Every method re-reads the product under a row lock (SELECT ... FOR UPDATE)
 * inside a transaction, so concurrent callers are serialised and stock can
 * never be corrupted or driven negative. Combined with the unsignedInteger
 * column, negative stock is impossible at both the app and DB layers.
 */
class InventoryService
{
    /**
     * Strict reservation for online orders. Throws if a tracked product has
     * insufficient stock — never floors silently.
     *
     * @throws InsufficientStockException
     */
    public function reserve(Product $product, int $qty, string $reference): void
    {
        DB::transaction(function () use ($product, $qty, $reference) {
            $locked = $this->lock($product);

            if (! $locked) {
                throw new InsufficientStockException('One of the items in your cart is no longer available.');
            }

            if (! $locked->track_stock) {
                return;
            }

            if ($locked->stock_quantity < $qty) {
                throw InsufficientStockException::for($locked->name, (int) $locked->stock_quantity, $qty);
            }

            $this->writeAdjustment($locked, -$qty, StockAdjustment::TYPE_SALE, ['notes' => "Online order {$reference}"]);
        });
    }

    /**
     * Lenient decrement (POS / internal). Floors at zero so it can never go
     * negative, mirroring the legacy behaviour but now row-locked.
     */
    public function decrement(Product $product, int $qty, string $type = StockAdjustment::TYPE_SALE, array $extra = []): void
    {
        DB::transaction(function () use ($product, $qty, $type, $extra) {
            $locked = $this->lock($product);

            if (! $locked || ! $locked->track_stock) {
                return;
            }

            $change = -min($qty, (int) $locked->stock_quantity); // floor at zero
            $this->writeAdjustment($locked, $change, $type, $extra);
        });
    }

    public function increment(Product $product, int $qty, string $type = StockAdjustment::TYPE_MANUAL_IN, array $extra = []): void
    {
        DB::transaction(function () use ($product, $qty, $type, $extra) {
            $locked = $this->lock($product);
            if (! $locked) {
                return;
            }

            $this->writeAdjustment($locked, +$qty, $type, $extra);
        });
    }

    /** Set stock to a counted physical figure (stock take). */
    public function setCount(Product $product, int $physicalCount, string $notes = ''): void
    {
        $physicalCount = max(0, $physicalCount);

        DB::transaction(function () use ($product, $physicalCount, $notes) {
            $locked = $this->lock($product);
            if (! $locked) {
                return;
            }

            $before = (int) $locked->stock_quantity;
            $this->writeAdjustment($locked, $physicalCount - $before, StockAdjustment::TYPE_STOCKTAKE, ['notes' => $notes], $physicalCount);
        });
    }

    private function lock(Product $product): ?Product
    {
        return Product::whereKey($product->getKey())->lockForUpdate()->first();
    }

    private function writeAdjustment(Product $product, int $change, string $type, array $extra, ?int $absolute = null): void
    {
        $before = (int) $product->stock_quantity;
        $after  = $absolute ?? max(0, $before + $change);

        $product->update(['stock_quantity' => $after]);

        StockAdjustment::create(array_merge([
            'product_id'      => $product->id,
            'type'            => $type,
            'quantity_before' => $before,
            'quantity_change' => $after - $before,
            'quantity_after'  => $after,
            'created_by'      => auth()->id(),
        ], $extra));
    }
}
