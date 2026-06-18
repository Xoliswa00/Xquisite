<x-app-layout>
    <x-slot name="header">Sales History</x-slot>

    <div class="space-y-4">

        <!-- Summary stats -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Today's Revenue</p>
                <p class="text-2xl font-bold text-white mt-1">R{{ number_format($todayTotal, 2) }}</p>
            </div>
            <div class="bg-slate-800 rounded-xl p-5">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Today's Sales</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $todayCount }}</p>
            </div>
        </div>

        <!-- Filters + POS button -->
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Reference or customer…"
                       class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 w-full sm:w-52 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                <input type="date" name="date" value="{{ request('date') }}"
                       class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                <select name="method" class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    <option value="">All methods</option>
                    @foreach(['cash','card','eft','split'] as $m)
                        <option value="{{ $m }}" @selected(request('method') === $m)>{{ strtoupper($m) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Filter</button>
                @if(request()->hasAny(['search','date','method']))
                    <a href="{{ route('pos.sales.index') }}" class="text-sm px-4 py-2 rounded-lg text-slate-400 hover:text-white">Clear</a>
                @endif
            </form>
            <a href="{{ route('pos.terminal') }}"
               class="w-full sm:w-auto text-center bg-emerald-600 hover:bg-emerald-500 text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                Open POS Terminal
            </a>
        </div>

        <div class="bg-slate-800 rounded-xl overflow-hidden">

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-slate-700">
                @forelse($sales as $sale)
                    <a href="{{ route('pos.sales.show', $sale) }}" class="block px-4 py-3 hover:bg-slate-700/50 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-xs text-[#0078D4]">{{ $sale->reference }}</span>
                            @if($sale->status === 'paid')
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs bg-emerald-900/50 text-emerald-400 border border-emerald-800">Paid</span>
                            @elseif($sale->status === 'voided')
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs bg-red-900/50 text-red-400 border border-red-800">Voided</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between mt-0.5">
                            <p class="text-sm text-slate-300">{{ $sale->customer?->name ?? 'Walk-in' }}</p>
                            <p class="text-sm font-bold text-white">R{{ number_format($sale->total, 2) }}</p>
                        </div>
                        <div class="flex items-center gap-3 mt-0.5">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-300">{{ strtoupper($sale->payment_method) }}</span>
                            <p class="text-xs text-slate-400">{{ $sale->paid_at?->format('d M Y, H:i') }}</p>
                        </div>
                    </a>
                @empty
                    <div class="px-4 py-10 text-center text-slate-500 text-sm">No sales yet.</div>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <table class="hidden sm:table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Reference</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Items</th>
                        <th class="px-4 py-3 font-medium">Payment</th>
                        <th class="px-4 py-3 font-medium">Total</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3 font-mono text-[#0078D4]">{{ $sale->reference }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $sale->items->count() }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-slate-700 text-slate-300">{{ strtoupper($sale->payment_method) }}</span>
                            </td>
                            <td class="px-4 py-3 font-bold text-white">R{{ number_format($sale->total, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($sale->status === 'paid')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-emerald-900/50 text-emerald-400 border border-emerald-800">Paid</span>
                                @elseif($sale->status === 'voided')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-red-900/50 text-red-400 border border-red-800">Voided</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $sale->paid_at?->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('pos.sales.show', $sale) }}" class="text-slate-400 hover:text-white text-xs">Receipt</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-slate-500">No sales yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $sales->links() }}
    </div>
</x-app-layout>
