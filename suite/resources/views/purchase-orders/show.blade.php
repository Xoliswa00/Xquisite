<x-app-layout>
    <x-slot name="header">Purchase Order {{ $purchaseOrder->reference }}</x-slot>

    <div class="max-w-4xl space-y-4">

        <!-- Header card -->
        <div class="bg-slate-800 rounded-xl p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-mono text-xl font-bold text-white">{{ $purchaseOrder->reference }}</span>
                        @php
                            $statusClasses = [
                                'draft'     => 'bg-slate-700 text-slate-300 border-slate-600',
                                'sent'      => 'bg-blue-900/50 text-blue-400 border-blue-800',
                                'partial'   => 'bg-yellow-900/50 text-yellow-400 border-yellow-800',
                                'received'  => 'bg-emerald-900/50 text-emerald-400 border-emerald-800',
                                'cancelled' => 'bg-red-900/50 text-red-400 border-red-800',
                            ];
                            $cls = $statusClasses[$purchaseOrder->status] ?? 'bg-slate-700 text-slate-300 border-slate-600';
                        @endphp
                        <span class="inline-flex px-2.5 py-1 rounded-full text-sm border {{ $cls }}">
                            {{ ucfirst($purchaseOrder->status) }}
                        </span>
                    </div>

                    @if($purchaseOrder->supplier)
                        <p class="text-sm text-slate-400">
                            Supplier: <span class="text-white">{{ $purchaseOrder->supplier }}</span>
                            @if($purchaseOrder->supplier_contact)
                                <span class="text-slate-600 mx-1">·</span>
                                {{ $purchaseOrder->supplier_contact }}
                            @endif
                        </p>
                    @endif

                    @if($purchaseOrder->notes)
                        <p class="text-sm text-slate-500 mt-1">{{ $purchaseOrder->notes }}</p>
                    @endif
                </div>

                <div class="text-right text-sm text-slate-400 space-y-1 shrink-0">
                    <p>Created {{ $purchaseOrder->created_at->format('d M Y') }}</p>
                    @if($purchaseOrder->sent_at)
                        <p>Sent {{ $purchaseOrder->sent_at->format('d M Y') }}</p>
                    @endif
                    @if($purchaseOrder->received_at)
                        <p class="text-emerald-400">Received {{ $purchaseOrder->received_at->format('d M Y') }}</p>
                    @endif
                </div>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap items-center gap-2 mt-4 pt-4 border-t border-slate-700">
                @if($purchaseOrder->status === 'draft')
                    <form method="POST" action="{{ route('purchase-orders.send', $purchaseOrder) }}">
                        @csrf
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-500 text-white text-sm px-5 py-2 rounded-lg">
                            Mark as Sent to Supplier
                        </button>
                    </form>
                @endif

                @if(in_array($purchaseOrder->status, ['draft', 'sent']))
                    <form method="POST" action="{{ route('purchase-orders.cancel', $purchaseOrder) }}"
                          onsubmit="return confirm('Cancel this purchase order?')">
                        @csrf
                        <button type="submit"
                                class="bg-red-900/40 hover:bg-red-900/70 text-red-400 border border-red-800 text-sm px-5 py-2 rounded-lg">
                            Cancel Order
                        </button>
                    </form>
                @endif

                <a href="{{ route('purchase-orders.index') }}" class="ml-auto text-sm text-slate-400 hover:text-white">
                    ← All Orders
                </a>
            </div>
        </div>

        <!-- Items + receive form -->
        @php $canReceive = in_array($purchaseOrder->status, ['sent', 'partial']); @endphp

        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700 flex items-center justify-between">
                <h3 class="text-sm font-medium text-slate-300">Order Items</h3>
                @if($purchaseOrder->status === 'partial')
                    <span class="text-xs text-yellow-400">Partially received — enter remaining quantities below</span>
                @elseif($purchaseOrder->status === 'sent')
                    <span class="text-xs text-blue-400">Enter received quantities when stock arrives</span>
                @endif
            </div>

            <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder) }}">
                @csrf

                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-[640px]">
                    <thead>
                        <tr class="border-b border-slate-700 text-slate-400 text-left">
                            <th class="px-4 py-3 font-medium">Product</th>
                            <th class="px-4 py-3 font-medium text-right">Ordered</th>
                            <th class="px-4 py-3 font-medium text-right">Received</th>
                            <th class="px-4 py-3 font-medium text-right">Remaining</th>
                            <th class="px-4 py-3 font-medium text-right">Unit Cost</th>
                            <th class="px-4 py-3 font-medium text-right">Subtotal</th>
                            @if($canReceive)
                                <th class="px-4 py-3 font-medium text-center w-36">Receive Now</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @foreach($purchaseOrder->items as $item)
                            <tr class="hover:bg-slate-700/30">
                                <td class="px-4 py-3">
                                    <p class="text-white font-medium">{{ $item->product_name }}</p>
                                    @if($item->product?->sku)
                                        <p class="text-xs text-slate-500">{{ $item->product->sku }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-slate-400">{{ $item->quantity_ordered }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($item->quantity_received >= $item->quantity_ordered)
                                        <span class="text-emerald-400 font-medium">{{ $item->quantity_received }}</span>
                                    @elseif($item->quantity_received > 0)
                                        <span class="text-yellow-400 font-medium">{{ $item->quantity_received }}</span>
                                    @else
                                        <span class="text-slate-600">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right text-slate-400">{{ $item->remaining }}</td>
                                <td class="px-4 py-3 text-right text-slate-400">R {{ number_format($item->unit_cost, 2) }}</td>
                                <td class="px-4 py-3 text-right text-white font-medium">R {{ number_format($item->subtotal, 2) }}</td>
                                @if($canReceive)
                                    <td class="px-4 py-3 text-center">
                                        @if($item->remaining > 0)
                                            <input type="number"
                                                   name="received[{{ $item->id }}]"
                                                   min="0" max="{{ $item->remaining }}"
                                                   placeholder="{{ $item->remaining }}"
                                                   class="w-24 bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-1.5 text-center focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                        @else
                                            <span class="text-emerald-400 text-xs">Complete</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-700">
                            <td colspan="{{ $canReceive ? 5 : 5 }}" class="px-4 py-3 text-right text-slate-400 font-medium">
                                Total Cost
                            </td>
                            <td class="px-4 py-3 text-right text-xl font-bold text-white">
                                R {{ number_format($purchaseOrder->total_cost, 2) }}
                            </td>
                            @if($canReceive)<td></td>@endif
                        </tr>
                    </tfoot>
                </table>
                </div>

                @if($canReceive)
                    <div class="px-4 py-3 border-t border-slate-700 flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-slate-500">
                            Leave a field blank to skip that item. Stock levels update immediately on save.
                        </p>
                        <button type="submit"
                                class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm px-6 py-2 rounded-lg">
                            Record Receipt
                        </button>
                    </div>
                @endif

            </form>
        </div>

    </div>
</x-app-layout>
