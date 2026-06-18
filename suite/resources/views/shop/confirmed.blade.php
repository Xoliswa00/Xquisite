<x-shop-layout :tenant="$tenant">

    <div class="max-w-2xl mx-auto text-center">

        <!-- Success icon -->
        <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-5">
            <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Order Confirmed!</h1>
        <p class="text-sm text-gray-500 mb-1">Thank you, {{ $order->customer_name }}.</p>
        <p class="text-xs text-gray-400 mb-8">
            A confirmation email has been sent to <span class="font-medium text-gray-600">{{ $order->customer_email }}</span>
        </p>

        <!-- Order reference card -->
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6 text-left">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900">Order Details</h2>
                <span class="text-xs font-mono font-semibold text-[#0078D4] bg-[#F0F7FF] px-2 py-1 rounded-lg">
                    {{ $order->reference }}
                </span>
            </div>

            <div class="space-y-3 mb-4">
                @foreach($order->items as $item)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-100 rounded-lg overflow-hidden shrink-0">
                            @if($item->product_image_url)
                                <img src="{{ $item->product_image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-400">× {{ $item->quantity }} @ R{{ number_format($item->unit_price, 2) }}</p>
                        </div>
                        <p class="text-sm font-semibold text-gray-900 shrink-0">R{{ number_format($item->subtotal, 2) }}</p>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-gray-100 pt-3 space-y-1.5">
                <div class="flex justify-between text-xs text-gray-500">
                    <span>Subtotal</span>
                    <span>R{{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->shipping_cost > 0)
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Shipping</span>
                        <span>R{{ number_format($order->shipping_cost, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold text-sm pt-2 border-t border-gray-100">
                    <span>Total</span>
                    <span class="text-[#0078D4]">R{{ number_format($order->total, 2) }}</span>
                </div>
            </div>

            <!-- Fulfillment + Payment info -->
            <div class="mt-4 grid sm:grid-cols-2 gap-3">
                <div class="bg-gray-50 rounded-xl px-4 py-3">
                    <p class="text-xs text-gray-400 mb-0.5">Fulfillment</p>
                    <p class="text-sm font-medium text-gray-900 capitalize">{{ $order->fulfillment_type }}</p>
                    @if($order->fulfillment_type === 'delivery' && $order->shipping_address)
                        <p class="text-xs text-gray-500 mt-1 leading-snug">
                            {{ $order->shipping_address['line1'] }},
                            {{ $order->shipping_address['city'] }}
                            @if($order->shipping_address['postal'])
                                , {{ $order->shipping_address['postal'] }}
                            @endif
                        </p>
                    @endif
                </div>
                <div class="bg-gray-50 rounded-xl px-4 py-3">
                    <p class="text-xs text-gray-400 mb-0.5">Payment</p>
                    <p class="text-sm font-medium text-gray-900">
                        @if($order->payment_method === 'payfast') Online (PayFast)
                        @elseif($order->payment_method === 'eft') Manual EFT
                        @else Pay on Collection
                        @endif
                    </p>
                    <p class="text-xs mt-1 font-medium {{ $order->payment_status === 'paid' ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $order->payment_status === 'paid' ? 'Paid' : 'Awaiting payment' }}
                    </p>
                </div>
            </div>

            @if($order->payment_method === 'eft')
                <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-xs text-blue-700">
                    <p class="font-semibold mb-1">EFT Instructions</p>
                    <p>Please use your order reference <strong>{{ $order->reference }}</strong> as the payment reference when making your bank transfer. Your order will be processed once payment is received.</p>
                </div>
            @endif
        </div>

        <a href="{{ route('shop.index', $tenant->slug) }}"
           class="inline-block bg-[#0078D4] hover:bg-[#002B5B] text-white font-semibold px-8 py-3 rounded-xl text-sm transition-colors">
            Continue Shopping
        </a>
    </div>

</x-shop-layout>
