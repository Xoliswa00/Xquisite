<x-app-layout>
    <x-slot name="header">Purchase Orders</x-slot>

    <div class="max-w-5xl space-y-4">

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-end gap-2">
            <a href="{{ route('purchase-orders.create', ['from_reorder' => 1]) }}"
               class="w-full sm:w-auto text-center text-sm text-amber-400 hover:text-amber-300 bg-amber-900/20 border border-amber-800/50 px-4 py-2 rounded-lg">
                From Reorder List
            </a>
            <a href="{{ route('purchase-orders.create') }}"
               class="w-full sm:w-auto text-center bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-5 py-2 rounded-lg">
                New Order
            </a>
        </div>

        <div class="bg-slate-800 rounded-xl overflow-hidden">

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-slate-700">
                @forelse($orders as $order)
                    @php
                        $statusClasses = ['draft'=>'bg-slate-700 text-slate-300 border-slate-600','sent'=>'bg-blue-900/50 text-blue-400 border-blue-800','partial'=>'bg-yellow-900/50 text-yellow-400 border-yellow-800','received'=>'bg-emerald-900/50 text-emerald-400 border-emerald-800','cancelled'=>'bg-red-900/50 text-red-400 border-red-800'];
                        $cls = $statusClasses[$order->status] ?? 'bg-slate-700 text-slate-300 border-slate-600';
                    @endphp
                    <a href="{{ route('purchase-orders.show', $order) }}" class="block px-4 py-3 hover:bg-slate-700/30 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-sm text-white">{{ $order->reference }}</span>
                            <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs border {{ $cls }}">{{ ucfirst($order->status) }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-0.5">
                            <p class="text-xs text-slate-400">{{ $order->supplier ?: 'No supplier' }} · {{ $order->items_count }} items</p>
                            <p class="text-sm font-medium text-white">R {{ number_format($order->total_cost, 2) }}</p>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $order->created_at->format('d M Y') }}</p>
                    </a>
                @empty
                    <div class="px-4 py-12 text-center text-slate-500 text-sm">
                        No purchase orders yet.
                        <a href="{{ route('purchase-orders.create') }}" class="text-indigo-400 hover:text-indigo-300 ml-1">Create one.</a>
                    </div>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <table class="hidden sm:table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Reference</th>
                        <th class="px-4 py-3 font-medium">Supplier</th>
                        <th class="px-4 py-3 font-medium text-center">Items</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium text-right">Total</th>
                        <th class="px-4 py-3 font-medium">Created</th>
                        <th class="px-4 py-3 font-medium text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($orders as $order)
                        @php
                            $statusClasses = ['draft'=>'bg-slate-700 text-slate-300 border-slate-600','sent'=>'bg-blue-900/50 text-blue-400 border-blue-800','partial'=>'bg-yellow-900/50 text-yellow-400 border-yellow-800','received'=>'bg-emerald-900/50 text-emerald-400 border-emerald-800','cancelled'=>'bg-red-900/50 text-red-400 border-red-800'];
                            $cls = $statusClasses[$order->status] ?? 'bg-slate-700 text-slate-300 border-slate-600';
                        @endphp
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3"><span class="font-mono text-white">{{ $order->reference }}</span></td>
                            <td class="px-4 py-3 text-slate-400">{{ $order->supplier ?: '—' }}</td>
                            <td class="px-4 py-3 text-center text-slate-400">{{ $order->items_count }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs border {{ $cls }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-white font-medium">R {{ number_format($order->total_cost, 2) }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $order->created_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('purchase-orders.show', $order) }}" class="text-xs text-indigo-400 hover:text-indigo-300">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                No purchase orders yet.
                                <a href="{{ route('purchase-orders.create') }}" class="text-indigo-400 hover:text-indigo-300 ml-1">Create one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($orders->hasPages())
                <div class="px-4 py-3 border-t border-slate-700">{{ $orders->links() }}</div>
            @endif
        </div>

    </div>
</x-app-layout>
