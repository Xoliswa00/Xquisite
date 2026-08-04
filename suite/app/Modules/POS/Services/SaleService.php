<?php

namespace App\Modules\POS\Services;

use App\Models\Tenant;
use App\Modules\Booking\Models\Appointment;
use App\Modules\POS\Actions\ReserveSaleInventory;
use App\Modules\POS\Models\Sale;
use App\Modules\POS\Models\SaleItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Orchestrates POS checkout safely, mirroring the online storefront's
 * OrderService:
 *   - idempotent (one logical checkout = one sale, even on double-clicks,
 *     refreshes, or a replayed offline-queue entry),
 *   - inventory revalidated and reserved under row locks (strict — throws on
 *     insufficient stock rather than silently flooring at zero),
 *   - fully transactional (all-or-nothing).
 *
 * Inspect Sale::$wasRecentlyCreated on the result to tell a fresh sale from
 * an idempotent replay.
 */
class SaleService
{
    public function __construct(private readonly ReserveSaleInventory $reserveInventory) {}

    public function checkout(Tenant $tenant, array $data, string $idempotencyKey): Sale
    {
        // Fast path: this checkout was already processed.
        if ($existing = $this->findByKey($tenant, $idempotencyKey)) {
            return $existing;
        }

        try {
            return DB::transaction(function () use ($tenant, $data, $idempotencyKey) {
                $items    = $data['items'];
                $discount = (float) ($data['discount'] ?? 0);
                $subtotal = collect($items)->sum(fn ($i) => $i['price'] * $i['qty']);
                $total    = max(0, $subtotal - $discount);

                $sale = Sale::create([
                    'tenant_id'       => $tenant->id,
                    'reference'       => Sale::generateReference(),
                    'idempotency_key' => $idempotencyKey,
                    'appointment_id'  => $data['appointment_id'] ?? null,
                    'customer_id'     => $data['customer_id'] ?? null,
                    'status'          => 'paid',
                    'subtotal'        => $subtotal,
                    'discount_amount' => $discount,
                    'tax_amount'      => 0,
                    'total'           => $total,
                    'payment_method'  => $data['payment_method'],
                    'notes'           => $data['notes'] ?? null,
                    'paid_at'         => now(),
                ]);

                $sale->assignSequentialReference();

                // Revalidate + reserve stock before recording line items, so
                // insufficient stock rolls back the whole sale rather than
                // leaving a half-recorded one.
                $this->reserveInventory->handle($tenant, $items, $sale->reference);

                foreach ($items as $item) {
                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'item_type'  => $item['type'],
                        'item_id'    => $item['id'],
                        'name'       => $item['name'],
                        'unit_price' => $item['price'],
                        'quantity'   => $item['qty'],
                        'subtotal'   => $item['price'] * $item['qty'],
                    ]);
                }

                if (! empty($data['appointment_id'])) {
                    Appointment::where('id', $data['appointment_id'])->update([
                        'pos_order_id' => $sale->id,
                        'status'       => 'completed',
                    ]);
                }

                return $sale;
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

    private function findByKey(Tenant $tenant, string $idempotencyKey): ?Sale
    {
        return Sale::where('tenant_id', $tenant->id)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    private function isDuplicateKeyViolation(QueryException $e): bool
    {
        return in_array($e->getCode(), ['23000', '23505'], true)
            || str_contains(strtolower($e->getMessage()), 'unique');
    }
}
