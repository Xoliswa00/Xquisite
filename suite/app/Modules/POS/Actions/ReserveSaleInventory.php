<?php

namespace App\Modules\POS\Actions;

use App\Models\Tenant;
use App\Modules\Ecommerce\Exceptions\InsufficientStockException;
use App\Modules\POS\Models\Product;
use App\Modules\POS\Services\InventoryService;

/**
 * Re-validates and reserves stock for the product line items in a POS sale.
 *
 * MUST be called inside a database transaction (SaleService provides one).
 * Service line items are skipped entirely — only products carry stock.
 * Prices are trusted from the submitted item (the POS terminal is an
 * internal, authenticated staff tool, not a public cart), unlike the online
 * storefront's equivalent which re-derives price server-side.
 */
class ReserveSaleInventory
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * @param  array<int,array{type:string,id:int,qty:int}>  $items
     */
    public function handle(Tenant $tenant, array $items, string $reference): void
    {
        foreach ($items as $item) {
            if (($item['type'] ?? null) !== 'product') {
                continue;
            }

            $qty = (int) $item['qty'];
            if ($qty < 1) {
                continue;
            }

            $product = Product::where('tenant_id', $tenant->id)
                ->where('id', $item['id'])
                ->where('is_active', true)
                ->first();

            if (! $product) {
                throw new InsufficientStockException('One of the items in this sale is no longer available.');
            }

            // Locks the row, revalidates, and decrements (or throws).
            $this->inventory->reserve($product, $qty, $reference);
        }
    }
}
