<?php

namespace App\Console\Commands;

use App\Modules\Ecommerce\Models\Order;
use App\Modules\Ecommerce\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cancels PayFast orders that were initiated but never completed (the customer
 * abandoned the gateway), and releases the stock that was reserved for them.
 */
class ExpireStalePendingOrders extends Command
{
    protected $signature   = 'ecommerce:expire-pending-orders {--minutes=30 : Age in minutes after which a pending PayFast order expires}';
    protected $description = 'Cancel stale pending PayFast orders and release their reserved stock';

    public function handle(OrderService $orders): int
    {
        $minutes = (int) $this->option('minutes');
        $cutoff  = now()->subMinutes($minutes);

        $stale = Order::query()
            ->where('payment_method', 'payfast')
            ->where('status', Order::STATUS_PENDING)
            ->where('payment_status', 'pending')
            ->whereNotNull('payment_initiated_at')
            ->where('payment_initiated_at', '<', $cutoff)
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No stale pending orders.');
            return self::SUCCESS;
        }

        foreach ($stale as $order) {
            $order->update([
                'status'         => Order::STATUS_CANCELLED,
                'payment_status' => 'failed',
            ]);

            $order->load('items');
            $orders->releaseInventory($order);

            Log::info('Expired stale pending order', ['order' => $order->reference]);
            $this->line("Expired {$order->reference}");
        }

        $this->info("Expired {$stale->count()} order(s).");

        return self::SUCCESS;
    }
}
