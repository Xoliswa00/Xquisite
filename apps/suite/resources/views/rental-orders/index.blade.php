<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold">Rental Orders</h2>
                @if ($overdueCount > 0)
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-900/50 text-red-400 border border-red-800">
                        {{ $overdueCount }} overdue
                    </span>
                @endif
            </div>
            <a href="{{ route('rental-orders.create') }}"
               class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium">
                + New Rental
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-lg text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="min-w-full divide-y divide-slate-700">
                <thead class="bg-slate-700/50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400 uppercase">Ref</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400 uppercase">Item</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400 uppercase">Customer</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-400 uppercase">Qty</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400 uppercase">Event Date</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400 uppercase">Return By</th>
                        <th class="px-5 py-3 text-right text-xs font-medium text-slate-400 uppercase">Charge</th>
                        <th class="px-5 py-3 text-left text-xs font-medium text-slate-400 uppercase">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse ($orders as $order)
                    @php
                        $statusColours = [
                            'reserved' => 'bg-blue-900/40 text-blue-400 border-blue-800',
                            'out'      => 'bg-amber-900/40 text-amber-400 border-amber-800',
                            'returned' => 'bg-emerald-900/40 text-emerald-400 border-emerald-800',
                            'overdue'  => 'bg-red-900/40 text-red-400 border-red-800',
                            'damaged'  => 'bg-red-900/60 text-red-300 border-red-700',
                        ];
                        $isOverdue = $order->isOverdue();
                    @endphp
                    <tr class="hover:bg-slate-700/30 transition {{ $isOverdue ? 'bg-red-950/20' : '' }}">
                        <td class="px-5 py-4 font-mono text-xs text-slate-400">{{ $order->reference }}</td>
                        <td class="px-5 py-4 text-sm font-medium text-slate-100">{{ $order->product->name }}</td>
                        <td class="px-5 py-4 text-sm text-slate-400">{{ $order->customer?->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-sm text-slate-300 text-right">{{ $order->quantity }}</td>
                        <td class="px-5 py-4 text-sm text-slate-300">{{ $order->event_date->format('d M Y') }}</td>
                        <td class="px-5 py-4 text-sm {{ $isOverdue ? 'text-red-400 font-medium' : 'text-slate-300' }}">
                            {{ $order->return_due_at->format('d M Y') }}
                            @if ($isOverdue) <span class="text-xs">(OVERDUE)</span> @endif
                        </td>
                        <td class="px-5 py-4 text-sm font-medium text-slate-100 text-right">R{{ number_format($order->totalCharge(), 2) }}</td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2 py-0.5 rounded-full border {{ $statusColours[$order->status] ?? 'bg-slate-700 text-slate-400 border-slate-600' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('rental-orders.show', $order) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-slate-500">
                            <p>No rental orders yet.</p>
                            <p class="text-sm mt-1">Add rentable products to your catalog, then create rental orders here.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($orders->hasPages())
                <div class="px-5 py-4 border-t border-slate-700">{{ $orders->links() }}</div>
            @endif
        </div>

    </div>
</x-app-layout>
