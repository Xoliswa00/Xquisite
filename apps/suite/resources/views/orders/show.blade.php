<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('orders.index') }}" class="text-slate-400 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <span class="font-mono text-indigo-400">{{ $order->reference }}</span>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Left: Items + Summary -->
        <div class="lg:col-span-2 space-y-4">

            <!-- Items -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-700">
                    <h2 class="text-sm font-semibold text-white">Items</h2>
                </div>
                <div class="divide-y divide-slate-700/50">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 px-5 py-4">
                            <div class="w-12 h-12 bg-slate-700 rounded-xl overflow-hidden shrink-0">
                                @if($item->product_image_url)
                                    <img src="{{ $item->product_image_url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">{{ $item->product_name }}</p>
                                @if($item->product_sku)
                                    <p class="text-xs text-slate-400">SKU: {{ $item->product_sku }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-slate-300">× {{ $item->quantity }} @ R{{ number_format($item->unit_price, 2) }}</p>
                                <p class="text-sm font-semibold text-white">R{{ number_format($item->subtotal, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="px-5 py-4 border-t border-slate-700 bg-slate-800/50 space-y-1.5">
                    <div class="flex justify-between text-xs text-slate-400">
                        <span>Subtotal</span>
                        <span>R{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->shipping_cost > 0)
                        <div class="flex justify-between text-xs text-slate-400">
                            <span>Shipping</span>
                            <span>R{{ number_format($order->shipping_cost, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-bold text-sm text-white pt-2 border-t border-slate-700">
                        <span>Total</span>
                        <span class="text-indigo-400">R{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer + Delivery -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Customer & Delivery</h2>
                <div class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Name</p>
                        <p class="text-white font-medium">{{ $order->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Email</p>
                        <p class="text-slate-300">{{ $order->customer_email }}</p>
                    </div>
                    @if($order->customer_phone)
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Phone</p>
                            <p class="text-slate-300">{{ $order->customer_phone }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs text-slate-400 mb-1">Fulfillment</p>
                        <p class="text-white capitalize font-medium">{{ $order->fulfillment_type }}</p>
                    </div>
                    @if($order->fulfillment_type === 'delivery' && $order->shipping_address)
                        <div class="sm:col-span-2">
                            <p class="text-xs text-slate-400 mb-1">Delivery Address</p>
                            <p class="text-slate-300 leading-snug">
                                {{ $order->shipping_address['line1'] }},
                                {{ $order->shipping_address['city'] }}
                                @if(!empty($order->shipping_address['province']))
                                    , {{ $order->shipping_address['province'] }}
                                @endif
                                @if(!empty($order->shipping_address['postal']))
                                    , {{ $order->shipping_address['postal'] }}
                                @endif
                            </p>
                        </div>
                    @endif
                    @if($order->notes)
                        <div class="sm:col-span-2">
                            <p class="text-xs text-slate-400 mb-1">Notes</p>
                            <p class="text-slate-300">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Status + Payment -->
        <div class="space-y-4">

            <!-- Status update -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Order Status</h2>

                @php
                    $statusColor = match($order->status) {
                        'pending'    => 'bg-slate-700 text-slate-300',
                        'paid'       => 'bg-blue-500/20 text-blue-300',
                        'processing' => 'bg-amber-500/20 text-amber-300',
                        'ready'      => 'bg-purple-500/20 text-purple-300',
                        'shipped'    => 'bg-cyan-500/20 text-cyan-300',
                        'delivered'  => 'bg-emerald-500/20 text-emerald-300',
                        'cancelled'  => 'bg-red-500/20 text-red-300',
                        'refunded'   => 'bg-orange-500/20 text-orange-300',
                        default      => 'bg-slate-700 text-slate-300',
                    };
                @endphp
                <div class="mb-4">
                    <span class="inline-flex text-xs font-semibold px-3 py-1 rounded-full {{ $statusColor }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>

                <form action="{{ route('orders.status', $order) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <select name="status"
                            class="w-full bg-slate-700 border border-slate-600 text-white text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['pending','paid','processing','ready','shipped','delivered','cancelled','refunded'] as $s)
                            <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2.5 rounded-xl transition-colors">
                        Update Status
                    </button>
                </form>
            </div>

            <!-- Payment info -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-white mb-4">Payment</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Method</span>
                        <span class="text-white font-medium">
                            @if($order->payment_method === 'payfast') PayFast
                            @elseif($order->payment_method === 'eft') EFT
                            @else On Collection
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Status</span>
                        <span class="{{ $order->payment_status === 'paid' ? 'text-emerald-400' : 'text-amber-400' }} font-medium">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    @if($order->paid_at)
                        <div class="flex justify-between">
                            <span class="text-slate-400">Paid at</span>
                            <span class="text-slate-300">{{ $order->paid_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                    @if($order->payfast_payment_id)
                        <div class="flex justify-between">
                            <span class="text-slate-400">PayFast ID</span>
                            <span class="text-xs font-mono text-slate-300">{{ $order->payfast_payment_id }}</span>
                        </div>
                    @endif
                    @if($order->fulfilled_at)
                        <div class="flex justify-between">
                            <span class="text-slate-400">Fulfilled</span>
                            <span class="text-slate-300">{{ $order->fulfilled_at->format('d M Y, H:i') }}</span>
                        </div>
                    @endif
                    <div class="pt-2 border-t border-slate-700 flex justify-between font-bold">
                        <span class="text-slate-300">Total</span>
                        <span class="text-indigo-400">R{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 p-5">
                <h2 class="text-sm font-semibold text-white mb-3">Timeline</h2>
                <div class="space-y-2 text-xs text-slate-400">
                    <div class="flex justify-between">
                        <span>Placed</span>
                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Updated</span>
                        <span>{{ $order->updated_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
