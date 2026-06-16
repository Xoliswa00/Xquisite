<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationEmail;
use App\Models\Tenant;
use App\Modules\Ecommerce\Models\Order;
use App\Modules\Ecommerce\Models\OrderItem;
use App\Services\Cart\CartService;
use App\Services\Payment\PayFastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    public function index(string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->where('is_active', true)->firstOrFail();
        $cart   = new CartService($tenantSlug);

        if ($cart->isEmpty()) {
            return redirect()->route('shop.index', $tenantSlug)->with('info', 'Your cart is empty.');
        }

        $lines    = $cart->lines($tenant->id);
        $subtotal = $cart->subtotal($tenant->id);

        return view('shop.checkout', compact('tenant', 'cart', 'lines', 'subtotal'));
    }

    public function place(Request $request, string $tenantSlug)
    {
        $tenant = Tenant::where('slug', $tenantSlug)->where('is_active', true)->firstOrFail();
        $cart   = new CartService($tenantSlug);

        if ($cart->isEmpty()) {
            return redirect()->route('shop.index', $tenantSlug);
        }

        $request->validate([
            'customer_name'     => 'required|string|max:255',
            'customer_email'    => 'required|email|max:255',
            'customer_phone'    => 'nullable|string|max:30',
            'fulfillment_type'  => 'required|in:collection,delivery',
            'payment_method'    => 'required|in:payfast,eft,collection',
            'notes'             => 'nullable|string|max:500',
            // Delivery address
            'address_line1'     => 'required_if:fulfillment_type,delivery|nullable|string|max:255',
            'address_city'      => 'required_if:fulfillment_type,delivery|nullable|string|max:100',
            'address_province'  => 'nullable|string|max:100',
            'address_postal'    => 'nullable|string|max:20',
        ]);

        $lines    = $cart->lines($tenant->id);
        $subtotal = $cart->subtotal($tenant->id);

        $shippingCost = $request->fulfillment_type === 'delivery' ? 0 : 0; // configure per tenant later
        $total        = $subtotal + $shippingCost;

        $order = DB::transaction(function () use ($request, $tenant, $lines, $subtotal, $shippingCost, $total) {
            $order = Order::create([
                'tenant_id'        => $tenant->id,
                'reference'        => Order::generateReference(),
                'customer_name'    => $request->customer_name,
                'customer_email'   => $request->customer_email,
                'customer_phone'   => $request->customer_phone,
                'fulfillment_type' => $request->fulfillment_type,
                'shipping_address' => $request->fulfillment_type === 'delivery' ? [
                    'line1'    => $request->address_line1,
                    'city'     => $request->address_city,
                    'province' => $request->address_province,
                    'postal'   => $request->address_postal,
                ] : null,
                'status'         => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $request->payment_method,
                'subtotal'       => $subtotal,
                'shipping_cost'  => $shippingCost,
                'total'          => $total,
                'notes'          => $request->notes,
            ]);

            foreach ($lines as $line) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'product_id'        => $line->product->id,
                    'product_name'      => $line->product->name,
                    'product_sku'       => $line->product->sku,
                    'product_image_url' => $line->product->image_url,
                    'unit_price'        => $line->product->price,
                    'quantity'          => $line->qty,
                    'subtotal'          => $line->subtotal,
                ]);

                // Reserve stock immediately for tracked products
                if ($line->product->track_stock) {
                    $line->product->decrementStock($line->qty, 'sale', [
                        'notes' => "Online order {$order->reference}",
                    ]);
                }
            }

            return $order;
        });

        $cart->clear();
        $order->load('items');

        // Collection or EFT — confirm immediately, send email
        if (in_array($request->payment_method, ['collection', 'eft'])) {
            if ($request->payment_method === 'collection') {
                $order->update(['status' => 'processing', 'payment_status' => 'pending']);
            }

            Mail::to($order->customer_email)->queue(new OrderConfirmationEmail($order, $tenant));

            return redirect()->route('shop.order.confirmed', [$tenantSlug, $order->reference]);
        }

        // PayFast — redirect to payment gateway
        $payfast     = new PayFastService();
        $paymentData = $payfast->buildPaymentData($order, $tenantSlug);

        return view('shop.payfast-redirect', [
            'paymentUrl'  => $payfast->getPaymentUrl(),
            'paymentData' => $paymentData,
        ]);
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

        if (!$payfast->validateIpn($request, $tenantSlug)) {
            abort(400, 'Invalid IPN signature');
        }

        $order = Order::where('tenant_id', $tenant->id)
            ->where('reference', $request->m_payment_id)
            ->first();

        if ($order && $request->payment_status === 'COMPLETE') {
            $order->update([
                'status'              => 'paid',
                'payment_status'      => 'paid',
                'payfast_payment_id'  => $request->pf_payment_id,
                'paid_at'             => now(),
            ]);

            $order->load('items');
            Mail::to($order->customer_email)->queue(new OrderConfirmationEmail($order, $tenant));
        }

        return response('OK', 200);
    }

    public function payfastReturn(string $tenantSlug)
    {
        // Redirect to a "payment processing" page — actual confirmation comes via IPN
        return redirect()->route('shop.index', $tenantSlug)
            ->with('info', 'Thank you! Your payment is being processed. You will receive a confirmation email shortly.');
    }

    public function payfastCancel(string $tenantSlug)
    {
        return redirect()->route('shop.checkout', $tenantSlug)
            ->with('error', 'Payment was cancelled. Your cart has been restored.');
    }
}
