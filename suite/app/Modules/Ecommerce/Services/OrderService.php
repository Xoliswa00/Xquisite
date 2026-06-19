<?php

namespace App\Modules\Ecommerce\Services;

use App\Models\Tenant;
use App\Modules\Ecommerce\Actions\CreateOrder;
use App\Modules\Ecommerce\Actions\ReserveInventory;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\POS\Models\Product;
use App\Services\Cart\CartService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Orchestrates placing an online order safely:
 *   - idempotent (one logical checkout = one order, even on retries/races),
 *   - inventory revalidated and reserved under row locks,
 *   - fully transactional (all-or-nothing).
 *
 * Inspect Order::$wasRecentlyCreated on the result to tell a fresh order
 * from an idempotent replay (e.g. to avoid sending a duplicate email).
 */
class OrderService
{
    public function __construct(
        private readonly ReserveInventory $reserveInventory,
        private readonly CreateOrder $createOrder,
    ) {}

    public function placeOrder(Tenant $tenant, array $data, CartService $cart, string $idempotencyKey): Order
    {
        // Fast path: this checkout was already processed.
        if ($existing = $this->findByKey($tenant, $idempotencyKey)) {
            return $existing;
        }

        $items = $cart->all(); // [product_id => qty]

        try {
            return DB::transaction(function () use ($tenant, $data, $items, $idempotencyKey) {
                // Pre-generate the reference for stock-adjustment notes.
                $reference = 'PENDING-' . Str::uuid();
                $lines     = $this->reserveInventory->handle($tenant, $items, $reference);

                return $this->createOrder->handle($tenant, $data, $lines, $idempotencyKey);
            });
        } catch (QueryException $e) {
            // Concurrent duplicate: the unique (tenant_id, idempotency_key)
            // constraint rejected the second insert — return the winner.
            if ($this->isDuplicateKeyViolation($e)) {
                return $this->findByKey($tenant, $idempotencyKey)
                    ?? throw $e;
            }

            throw $e;
        }
    }

    /**
     * Release reserved stock back to inventory (e.g. when a pending gateway
     * order is cancelled or expires).
     */
    public function releaseInventory(Order $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                $product = Product::where('id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($product && $product->track_stock) {
                    $product->incrementStock(
                        $item->quantity,
                        \App\Modules\POS\Models\StockAdjustment::TYPE_MANUAL_IN,
                        ['notes' => "Released from cancelled order {$order->reference}"]
                    );
                }
            }
        });
    }

    private function findByKey(Tenant $tenant, string $idempotencyKey): ?Order
    {
        return Order::where('tenant_id', $tenant->id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    private function isDuplicateKeyViolation(QueryException $e): bool
    {
        // 23000 (MySQL/SQLite) / 23505 (Postgres) integrity constraint violation.
        return in_array($e->getCode(), ['23000', '23505'], true)
            || str_contains(strtolower($e->getMessage()), 'unique');
    }
}
