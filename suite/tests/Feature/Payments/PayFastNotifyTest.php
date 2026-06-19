<?php

namespace Tests\Feature\Payments;

use App\Models\Tenant;
use App\Modules\Ecommerce\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PayFastNotifyTest extends TestCase
{
    use RefreshDatabase;

    private function tenant(): Tenant
    {
        return Tenant::create([
            'name'      => 'Test Store',
            'slug'      => 'test-store',
            'is_active' => true,
        ]);
    }

    private function pendingOrder(Tenant $tenant): Order
    {
        return Order::create([
            'tenant_id'            => $tenant->id,
            'reference'            => 'ORD-00001',
            'idempotency_key'      => 'key-1',
            'customer_name'        => 'Jane Doe',
            'customer_email'       => 'jane@example.com',
            'fulfillment_type'     => 'collection',
            'status'               => Order::STATUS_PENDING,
            'payment_status'       => 'pending',
            'payment_method'       => 'payfast',
            'subtotal'             => 150,
            'total'                => 150,
            'payment_initiated_at' => now(),
        ]);
    }

    /** Mirrors PayFastService::sign() so the test posts a valid signature. */
    private function sign(array $data): string
    {
        $parts = [];
        foreach ($data as $key => $value) {
            if ($value !== '' && $key !== 'signature') {
                $parts[] = $key . '=' . urlencode(trim((string) $value));
            }
        }
        $passphrase = config('payfast.passphrase', '');
        $str = implode('&', $parts);
        if ($passphrase !== '') {
            $str .= '&passphrase=' . urlencode(trim($passphrase));
        }
        return md5($str);
    }

    private function ipnPayload(Order $order, string $status = 'COMPLETE'): array
    {
        $data = [
            'm_payment_id'   => $order->reference,
            'pf_payment_id'  => '1234567',
            'payment_status' => $status,
            'amount_gross'   => '150.00',
        ];
        $data['signature'] = $this->sign($data);

        return $data;
    }

    public function test_valid_complete_ipn_marks_order_paid(): void
    {
        Mail::fake();
        $tenant = $this->tenant();
        $order  = $this->pendingOrder($tenant);

        $response = $this->post(route('shop.payfast.notify', 'test-store'), $this->ipnPayload($order));

        $response->assertOk();
        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(Order::STATUS_PAID, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame('1234567', $order->payfast_payment_id);
    }

    public function test_duplicate_ipn_is_ignored(): void
    {
        Mail::fake();
        $tenant = $this->tenant();
        $order  = $this->pendingOrder($tenant);
        $payload = $this->ipnPayload($order);

        $this->post(route('shop.payfast.notify', 'test-store'), $payload)->assertOk();
        $paidAt = $order->fresh()->paid_at;

        // Second identical callback must not error or change anything.
        $this->post(route('shop.payfast.notify', 'test-store'), $payload)->assertOk();

        $this->assertEquals($paidAt, $order->fresh()->paid_at);
        Mail::assertQueued(\App\Mail\OrderConfirmationEmail::class, 1);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $tenant = $this->tenant();
        $order  = $this->pendingOrder($tenant);

        $payload = $this->ipnPayload($order);
        $payload['signature'] = 'tampered';

        $this->post(route('shop.payfast.notify', 'test-store'), $payload)->assertStatus(400);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }
}
