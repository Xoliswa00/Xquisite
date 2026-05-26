<x-app-layout>
    <x-slot name="header">{{ $supplier->name }}</x-slot>

    <div class="max-w-4xl space-y-4">

        <!-- Supplier detail card -->
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-3">
                        <h2 class="text-lg font-semibold text-white">{{ $supplier->name }}</h2>
                        @if($supplier->is_active)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                        @else
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                        @endif
                    </div>
                    @if($supplier->contact_person)
                        <p class="text-sm text-slate-400">Contact: <span class="text-white">{{ $supplier->contact_person }}</span></p>
                    @endif
                    @if($supplier->email)
                        <p class="text-sm text-slate-400">Email: <span class="text-white">{{ $supplier->email }}</span></p>
                    @endif
                    @if($supplier->phone)
                        <p class="text-sm text-slate-400">Phone: <span class="text-white">{{ $supplier->phone }}</span></p>
                    @endif
                    @if($supplier->website)
                        <p class="text-sm text-slate-400">Website: <a href="{{ $supplier->website }}" target="_blank" class="text-indigo-400 hover:text-indigo-300">{{ $supplier->website }}</a></p>
                    @endif
                    @if($supplier->payment_terms)
                        <p class="text-sm text-slate-400">Payment Terms: <span class="text-white">{{ $supplier->payment_terms }}</span></p>
                    @endif
                    @if($supplier->address)
                        <p class="text-sm text-slate-400">Address: <span class="text-white">{{ $supplier->address }}</span></p>
                    @endif
                    @if($supplier->notes)
                        <p class="text-sm text-slate-500 mt-2">{{ $supplier->notes }}</p>
                    @endif
                </div>

                <div class="shrink-0 flex flex-col gap-2">
                    <a href="{{ route('suppliers.edit', $supplier) }}"
                       class="bg-slate-700 hover:bg-slate-600 text-white text-sm px-4 py-2 rounded-lg text-center">
                        Edit
                    </a>
                    <a href="{{ route('purchase-orders.create', ['supplier_id' => $supplier->id]) }}"
                       class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg text-center">
                        New PO
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 mt-5 pt-4 border-t border-slate-700">
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $supplier->products_count }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Linked Products</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $supplier->purchase_orders_count }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Total Orders</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-white">{{ $supplier->active_orders_count }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Active Orders</p>
                </div>
            </div>
        </div>

        <div class="grid lg:grid-cols-2 gap-4">

            <!-- Linked products -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-300">Linked Products</h3>
                    <a href="{{ route('products.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">All products →</a>
                </div>
                @forelse($products as $product)
                    <div class="px-4 py-3 border-b border-slate-700/50 flex items-center justify-between hover:bg-slate-700/30">
                        <div>
                            <p class="text-sm text-white">{{ $product->name }}</p>
                            @if($product->sku)
                                <p class="text-xs text-slate-500">{{ $product->sku }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm {{ $product->stock_status === 'out_of_stock' ? 'text-red-400' : ($product->stock_status === 'low' ? 'text-yellow-400' : 'text-white') }} font-medium">
                                {{ $product->stock_quantity }}
                            </p>
                            <p class="text-xs text-slate-500">in stock</p>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-slate-500 text-sm">No products linked yet.</div>
                @endforelse
            </div>

            <!-- Recent purchase orders -->
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-300">Recent Orders</h3>
                    <a href="{{ route('purchase-orders.index') }}" class="text-xs text-indigo-400 hover:text-indigo-300">All orders →</a>
                </div>
                @forelse($orders as $order)
                    @php
                        $cls = match($order->status) {
                            'sent'      => 'bg-blue-900/50 text-blue-400 border-blue-800',
                            'partial'   => 'bg-yellow-900/50 text-yellow-400 border-yellow-800',
                            'received'  => 'bg-emerald-900/50 text-emerald-400 border-emerald-800',
                            'cancelled' => 'bg-red-900/50 text-red-400 border-red-800',
                            default     => 'bg-slate-700 text-slate-300 border-slate-600',
                        };
                    @endphp
                    <a href="{{ route('purchase-orders.show', $order) }}"
                       class="px-4 py-3 border-b border-slate-700/50 flex items-center justify-between hover:bg-slate-700/30 block">
                        <div>
                            <p class="text-sm font-mono text-white">{{ $order->reference }}</p>
                            <p class="text-xs text-slate-500">{{ $order->created_at->format('d M Y') }} · {{ $order->items_count }} items</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs border {{ $cls }}">{{ ucfirst($order->status) }}</span>
                            <span class="text-sm text-white font-medium">R {{ number_format($order->total_cost, 2) }}</span>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center text-slate-500 text-sm">No orders for this supplier yet.</div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
