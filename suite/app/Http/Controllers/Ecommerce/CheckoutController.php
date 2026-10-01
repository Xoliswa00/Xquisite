<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\Concerns\ResolvesShopTenant;
use App\Mail\OrderConfirmationEmail;
use App\Models\Tenant;
use App\Modules\Ecommerce\Exceptions\InsufficientStockException;
use App\Modules\Ecommerce\Exceptions\PromoCodeExpiredException;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\Ecommerce\Services\OrderService;
use App\Rules\SouthAfricanPhoneNumber;
use App\Services\Cart\CartService;
use App\Services\Payment\PayFastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    use ResolvesShopTenant;

    public function __construct(private readonly OrderService $orders) {}

    public function index(string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);
        $cart   = new CartService($tenant->id);

        if ($cart->isEmpty()) {
            return redirect()->to($tenant->shopRoute('index'))->with('info', 'Your cart is empty.');
        }

        $lines      = $cart->lines($tenant->id);
        $comboLines = $cart->comboLines($tenant->id);
        $subtotal   = $cart->subtotal($tenant->id);
        $promotion  = $cart->promotion($tenant->id);
        $discount   = $promotion ? $promotion->discountFor($subtotal) : 0.0;

        // One idempotency token per checkout attempt, keyed by tenant ID so
        // it's the same token regardless of which URL (path or subdomain)
        // the shopper is on. Re-used across validation failures, regenerated
        // only after a successful order.
        $idempotencyKey = $this->idempotencyKey($tenant->id);

        return view('shop.checkout', compact('tenant', 'cart', 'lines', 'comboLines', 'subtotal', 'promotion', 'discount', 'idempotencyKey'));
    }

    public function place(Request $request, string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);
        $cart   = new CartService($tenant->id);

        if ($cart->isEmpty()) {
            return redirect()->to($tenant->shopRoute('index'))->with('info', 'Your cart is empty.');
        }

        $data = $request->validate([
            'customer_name'     => 'required|string|max:255',
            'customer_email'    => 'required|email|max:255',
            'customer_phone'    => ['nullable', new SouthAfricanPhoneNumber],
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
        $idempotencyKey = $this->idempotencyKey($tenant->id);

        try {
            $order = $this->orders->placeOrder($tenant, $data, $cart, $idempotencyKey);
        } catch (InsufficientStockException $e) {
            return redirect()->to($tenant->shopRoute('cart'))->with('error', $e->getMessage());
        } catch (PromoCodeExpiredException $e) {
            $cart->removePromoCode();

            return redirect()->to($tenant->shopRoute('cart'))->with('error', $e->getMessage());
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
        $this->forgetIdempotencyKey($tenant->id);
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

            return redirect()->to($tenant->shopRoute('order.confirmed', ['reference' => $order->reference]));
        }

        // Already paid (idempotent replay of a completed PayFast order) — skip the gateway.
        if ($order->isPaid()) {
            return redirect()->to($tenant->shopRoute('order.confirmed', ['reference' => $order->reference]));
        }

        // PayFast — hand off to the gateway.
        try {
            $payfast     = new PayFastService();
            $paymentData = $payfast->buildPaymentData($order, $tenant);

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
        $tenant = $this->activeShopTenant($tenantSlug);
        $order  = Order::where('tenant_id', $tenant->id)
            ->where('reference', $reference)
            ->with('items')
            ->firstOrFail();

        return view('shop.confirmed', compact('tenant', 'order'));
    }

    public function payfastNotify(Request $request, string $tenantSlug)
    {
        $tenant  = $this->activeShopTenant($tenantSlug);
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
        $tenant = $this->activeShopTenant($tenantSlug);

        return redirect()->to($tenant->shopRoute('index'))
            ->with('info', 'Thank you! Your payment is being processed. You will receive a confirmation email shortly.');
    }

    public function payfastCancel(string $tenantSlug)
    {
        $tenant = $this->activeShopTenant($tenantSlug);

        return redirect()->to($tenant->shopRoute('checkout'))
            ->with('error', 'Payment was cancelled. Your order is saved as pending — you can try paying again.');
    }

    // ── Helpers ────────────────────────────────────────────────

    /** Keyed by tenant ID — see CartService for why not the raw route identifier. */
    private function idempotencyKey(int $tenantId): string
    {
        $sessionKey = 'checkout_idem.' . $tenantId;

        if (! session()->has($sessionKey)) {
            session([$sessionKey => (string) Str::uuid()]);
        }

        return session($sessionKey);
    }

    private function forgetIdempotencyKey(int $tenantId): void
    {
        session()->forget('checkout_idem.' . $tenantId);
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
