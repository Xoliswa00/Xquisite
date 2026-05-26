<x-app-layout>
    <x-slot name="header">Receipt · {{ $sale->reference }}</x-slot>

    <div class="max-w-lg space-y-4">

        <!-- Receipt card -->
        <div class="bg-slate-800 rounded-xl overflow-hidden" id="receipt">

            <!-- Header -->
            <div class="px-6 py-5 border-b border-slate-700 text-center">
                <h1 class="text-xl font-bold text-white">Xquisite Suite</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ now()->format('d M Y') }}</p>
            </div>

            <!-- Meta -->
            <div class="px-6 py-4 border-b border-slate-700 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-slate-400">Reference</p>
                    <p class="font-mono text-white">{{ $sale->reference }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Date</p>
                    <p class="text-white">{{ $sale->paid_at?->format('d M Y, H:i') }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Customer</p>
                    <p class="text-white">{{ $sale->customer?->name ?? 'Walk-in' }}</p>
                </div>
                <div>
                    <p class="text-slate-400">Served by</p>
                    <p class="text-white">{{ $sale->appointment?->staff?->name ?? '—' }}</p>
                </div>
                @if($sale->appointment_id)
                    <div class="col-span-2">
                        <p class="text-slate-400">Appointment</p>
                        <a href="{{ route('appointments.show', $sale->appointment_id) }}"
                           class="text-indigo-400 hover:text-indigo-300">#{{ $sale->appointment_id }}</a>
                    </div>
                @endif
            </div>

            <!-- Line items -->
            <div class="px-6 py-4 border-b border-slate-700">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-slate-400 text-left border-b border-slate-700 pb-2">
                            <th class="pb-2 font-medium">Item</th>
                            <th class="pb-2 font-medium text-right">Qty</th>
                            <th class="pb-2 font-medium text-right">Price</th>
                            <th class="pb-2 font-medium text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($sale->items as $item)
                            <tr>
                                <td class="py-2.5">
                                    <p class="text-white">{{ $item->name }}</p>
                                    <span class="text-xs px-1.5 py-0.5 rounded {{ $item->item_type === 'service' ? 'bg-indigo-900/50 text-indigo-300' : 'bg-slate-700 text-slate-400' }}">
                                        {{ ucfirst($item->item_type) }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-right text-slate-300">{{ $item->quantity }}</td>
                                <td class="py-2.5 text-right text-slate-300">R{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-2.5 text-right text-white font-medium">R{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Totals -->
            <div class="px-6 py-4 space-y-2 text-sm border-b border-slate-700">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal</span>
                    <span>R{{ number_format($sale->subtotal, 2) }}</span>
                </div>
                @if($sale->discount_amount > 0)
                    <div class="flex justify-between text-emerald-400">
                        <span>Discount</span>
                        <span>−R{{ number_format($sale->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-white font-bold text-base pt-1 border-t border-slate-700">
                    <span>TOTAL</span>
                    <span>R{{ number_format($sale->total, 2) }}</span>
                </div>
                <div class="flex justify-between text-slate-400 text-xs">
                    <span>Payment</span>
                    <span>{{ strtoupper($sale->payment_method) }}</span>
                </div>
            </div>

            <!-- Status -->
            <div class="px-6 py-4 text-center">
                @if($sale->status === 'paid')
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">
                        ✓ Payment Received
                    </span>
                @elseif($sale->status === 'voided')
                    <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium bg-red-900/50 text-red-400 border border-red-800">
                        VOIDED
                    </span>
                @endif
                @if($sale->notes)
                    <p class="text-xs text-slate-500 mt-2">{{ $sale->notes }}</p>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
            <button onclick="window.print()"
                    class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">
                Print Receipt
            </button>
            @if($sale->status === 'paid')
                <form method="POST" action="{{ route('pos.sales.void', $sale) }}"
                      onsubmit="return confirm('Void this sale?')">
                    @csrf
                    <button type="submit" class="text-red-400 hover:text-red-300 text-sm">Void Sale</button>
                </form>
            @endif
            <a href="{{ route('pos.terminal') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white text-sm px-4 py-2 rounded-lg ml-auto">
                New Sale
            </a>
        </div>

        <a href="{{ route('pos.sales.index') }}" class="inline-block text-sm text-slate-400 hover:text-white">← Sales History</a>
    </div>
</x-app-layout>
