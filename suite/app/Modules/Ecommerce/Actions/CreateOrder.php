<?php

namespace App\Modules\Ecommerce\Actions;

use App\Models\Tenant;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\Ecommerce\Models\OrderItem;
use Illuminate\Support\Collection;

/**
 * Persists an Order and its line items from reserved inventory lines.
 *
 * MUST be called inside a transaction (alongside ReserveInventory). The order
 * reference is a ULID (Order::generateReference), globally unique and safe to
 * generate concurrently — free of the max(id)+1 race the legacy generator had.
 *
 * @param  Collection<int,object>  $lines  {product, qty, unit_price, subtotal}
 */
class CreateOrder
{
    public function handle(Tenant $tenant, array $data, Collection $lines, string $idempotencyKey): Order
    {
        $subtotal     = (float) $lines->sum('subtotal');
        $fulfillment  = $data['fulfillment_type'];
        $shippingCost = $tenant->calculateShipping($fulfillment);
        $total        = $subtotal + $shippingCost;
        $method       = $data['payment_method'];

        $order = Order::create([
            'tenant_id'        => $tenant->id,
            'reference'        => Order::generateReference(),
            'idempotency_key'  => $idempotencyKey,
            'customer_name'    => $data['customer_name'],
            'customer_email'   => $data['customer_email'],
            'customer_phone'   => $data['customer_phone'] ?? null,
            'fulfillment_type' => $fulfillment,
            'shipping_address' => $fulfillment === 'delivery' ? [
                'line1'    => $data['address_line1'] ?? null,
                'city'     => $data['address_city'] ?? null,
                'province' => $data['address_province'] ?? null,
                'postal'   => $data['address_postal'] ?? null,
            ] : null,
            'status'               => Order::STATUS_PENDING,
            'payment_status'       => 'pending',
            'payment_method'       => $method,
            'subtotal'             => $subtotal,
            'shipping_cost'        => $shippingCost,
            'total'                => $total,
            'notes'                => $data['notes'] ?? null,
            'payment_initiated_at' => $method === 'payfast' ? now() : null,
        ]);

        $order->reference = 'ORD-' . str_pad((string) $order->id, 5, '0', STR_PAD_LEFT);
        $order->save();

        foreach ($lines as $line) {
            OrderItem::create([
                'order_id'          => $order->id,
                'product_id'        => $line->product->id,
                'product_name'      => $line->product->name,
                'product_sku'       => $line->product->sku,
                'product_image_url' => $line->product->image_url,
                'unit_price'        => $line->unit_price,
                'quantity'          => $line->qty,
                'subtotal'          => $line->subtotal,
            ]);
        }

        return $order;
    }
}
