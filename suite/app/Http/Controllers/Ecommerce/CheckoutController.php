<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationEmail;
use App\Models\Tenant;
use App\Modules\Ecommerce\Exceptions\InsufficientStockException;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\Ecommerce\Services\OrderService;
use App\Services\Cart\CartService;
use App\Services\Payment\PayFastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->where('is_active', true)->firstOrFail();
        $cart   = new CartService($tenantSlug);

        if ($cart->isEmpty()) {
            return redirect()->route('shop.index', $tenantSlug)->with('info', 'Your cart is empty.');
        }

        $lines    = $cart->lines($tenant->id);
        $subtotal = $cart->subtotal($tenant->id);

        // One idempotency token per checkout attempt. Re-used across validation
        // failures, regenerated only after a successful order.
        $idempotencyKey = $this->idempotencyKey($tenantSlug);

        return view('shop.checkout', compact('tenant', 'cart', 'lines', 'subtotal', 'idempotencyKey'));
    }

    public function place(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->where('is_active', true)->firstOrFail();
        $cart   = new CartService($tenantSlug);

        if ($cart->isEmpty()) {
            return redirect()->route('shop.index', $tenantSlug)->with('info', 'Your cart is empty.');
        }

        $data = $request->validate([
            'customer_name'     => 'required|string|max:255',
            'customer_email'    => 'required|email|max:255',
            'customer_phone'    => 'nullable|string|max:30',
            'fulfillment_type'  => 'required|in:collection,delivery',
            'payment_method'    => 'required|in:payfast,eft,collection',
            'notes'             => 'nullable|string|max:500',
            'address_line1'     => 'required_if:fulfillment_type,delivery|nullable|string|max:255',
            'address_city'      => 'required_if:fulfillment_type,delivery|nullable|string|max:100',
            'address_province'  => 'nullable|string|max:100',
            'address_postal'    => 'nullable|string|max:20',
        ]);

        // The server-side session token is authoritative — a forged form field
        // cannot bypass idempotency.
        $idempotencyKey = $this->idempotencyKey($tenantSlug);

        try {
            $order = $this->orders->placeOrder($tenant, $data, $cart, $idempotencyKey);
        } catch (InsufficientStockException $e) {
            return redirect()->route('shop.cart', $tenantSlug)->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Online checkout failed', [
                'tenant' => $tenant->id,
                'error'  => $e->getMessage(),
            ]);

            return response()->view('shop.payment-failed', [
                'tenant'  => $tenant,
                'message' => 'We could not process your order. No payment was taken — please try again.',
            ], 500);
        }

        // Order is committed. Safe to clear the cart and rotate the token.
        $cart->clear();
        $this->forgetIdempotencyKey($tenantSlug);
        $order->load('items');

        // EFT / collection — confirm immediately. Only email on first creation
        // so an idempotent replay never double-sends.
        if (in_array($order->payment_method, ['collection', 'eft'], true)) {
            if ($order->wasRecentlyCreated) {
                if ($order->payment_method === 'collection') {
                    $order->update(['status' => Order::STATUS_PROCESSING]);
                }
                $this->safeMail($order, $tenant);
            }

            return redirect()->route('shop.order.confirmed', [$tenantSlug, $order->reference]);
        }

        // Already paid (idempotent replay of a completed PayFast order) — skip the gateway.
        if ($order->isPaid()) {
            return redirect()->route('shop.order.confirmed', [$tenantSlug, $order->reference]);
        }

        // PayFast — hand off to the gateway.
        try {
            $payfast     = new PayFastService();
            $paymentData = $payfast->buildPaymentData($order, $tenantSlug);

            return view('shop.payfast-redirect', [
                'paymentUrl'  => $payfast->getPaymentUrl(),
                'paymentData' => $paymentData,
            ]);
        } catch (\Throwable $e) {
            Log::error('PayFast handoff failed', [
                'order' => $order->reference,
                'error' => $e->getMessage(),
            ]);

            return response()->view('shop.payment-failed', [
                'tenant'  => $tenant,
                'message' => 'We could not reach the payment gateway. Your order ' . $order->reference
                    . ' is saved as pending — please try paying again or contact the store.',
            ], 500);
        }
    }

    public function confirmed(string $tenantSlug, string $reference)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $order  = Order::where('tenant_id', $tenant->id)
            ->where('reference', $reference)
            ->with('items')
            ->firstOrFail();

        return view('shop.confirmed', compact('tenant', 'order'));
    }

    public function payfastNotify(Request $request, string $tenantSlug)
    {
        $tenant  = Tenant::where('slug', $tenantSlug)->firstOrFail();
        $payfast = new PayFastService();

        if (! $payfast->validateIpn($request, $tenantSlug)) {
            Log::warning('PayFast IPN rejected (signature/IP)', [
                'tenant'  => $tenant->id,
                'payment' => $request->input('m_payment_id'),
                'ip'      => $request->ip(),
            ]);

            abort(400, 'Invalid IPN signature');
        }

        $order = Order::where('tenant_id', $tenant->id)
            ->where('reference', $request->input('m_payment_id'))
            ->first();

        if (! $order) {
            Log::warning('PayFast IPN for unknown order', [
                'tenant'  => $tenant->id,
                'payment' => $request->input('m_payment_id'),
            ]);

            return response('OK', 200); // 200 so PayFast stops retrying a dead reference
        }

        // Duplicate-callback guard: PayFast may send the same IPN several times.
        if ($order->isPaid()) {
            Log::info('PayFast IPN duplicate ignored', ['order' => $order->reference]);

            return response('OK', 200);
        }

        $status = strtoupper((string) $request->input('payment_status'));

        if ($status === 'COMPLETE') {
            $order->update([
                'status'             => Order::STATUS_PAID,
                'payment_status'     => 'paid',
                'payfast_payment_id' => $request->input('pf_payment_id'),
                'paid_at'            => now(),
            ]);

            $order->load('items');
            $this->safeMail($order, $tenant);

            Log::info('PayFast payment completed', ['order' => $order->reference]);
        } else {
            // Failed / cancelled — release reserved stock so it isn't stuck.
            $order->update(['payment_status' => 'failed', 'status' => Order::STATUS_CANCELLED]);
            $this->orders->releaseInventory($order);

            Log::info('PayFast payment not completed', [
                'order'  => $order->reference,
                'status' => $status,
            ]);
        }

        return response('OK', 200);
    }

    public function payfastReturn(string $tenantSlug)
    {
        return redirect()->route('shop.index', $tenantSlug)
            ->with('info', 'Thank you! Your payment is being processed. You will receive a confirmation email shortly.');
    }

    public function payfastCancel(string $tenantSlug)
    {
        return redirect()->route('shop.checkout', $tenantSlug)
            ->with('error', 'Payment was cancelled. Your order is saved as pending — you can try paying again.');
    }

    // ── Helpers ────────────────────────────────────────────────

    private function idempotencyKey(string $tenantSlug): string
    {
        $sessionKey = 'checkout_idem.' . $tenantSlug;

        if (! session()->has($sessionKey)) {
            session([$sessionKey => (string) Str::uuid()]);
        }

        return session($sessionKey);
    }

    private function forgetIdempotencyKey(string $tenantSlug): void
    {
        session()->forget('checkout_idem.' . $tenantSlug);
    }

    private function safeMail(Order $order, Tenant $tenant): void
    {
        try {
            Mail::to($order->customer_email)->queue(new OrderConfirmationEmail($order, $tenant));
        } catch (\Throwable $e) {
            // Never let a mail failure break the order/payment flow.
            Log::error('Order confirmation email failed', [
                'order' => $order->reference,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
