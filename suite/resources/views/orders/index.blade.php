<x-app-layout>
    <x-slot name="header">Orders</x-slot>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-slate-800 rounded-2xl p-4">
            <p class="text-xs text-slate-400 mb-1">Today's Revenue</p>
            <p class="text-xl font-bold text-white">R{{ number_format($todayTotal, 2) }}</p>
        </div>
        <div class="bg-slate-800 rounded-2xl p-4">
            <p class="text-xs text-slate-400 mb-1">Orders Today</p>
            <p class="text-xl font-bold text-white">{{ $todayCount }}</p>
        </div>
        <div class="col-span-2 sm:col-span-1 bg-slate-800 rounded-2xl p-4">
            <p class="text-xs text-slate-400 mb-1">Pending / Processing</p>
            <p class="text-xl font-bold text-amber-400">{{ $pendingCount }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" action="{{ route('orders.index') }}" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Reference, name, email…"
               class="bg-slate-800 border border-slate-700 text-sm text-white rounded-xl px-4 py-2.5 w-full sm:w-64 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="status"
                class="bg-slate-800 border border-slate-700 text-sm text-white rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All Statuses</option>
            @foreach(['pending','paid','processing','ready','shipped','delivered','cancelled','refunded'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ request('date') }}"
               class="bg-slate-800 border border-slate-700 text-sm text-white rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-colors">
            Filter
        </button>
        @if(request()->hasAny(['search','status','date']))
            <a href="{{ route('orders.index') }}"
               class="text-sm text-slate-400 hover:text-white px-3 py-2.5 transition-colors">Clear</a>
        @endif
    </form>

    <!-- Table -->
    <div class="bg-slate-800 rounded-2xl border border-slate-700 overflow-hidden">
        @if($orders->isEmpty())
            <div class="text-center py-16 text-slate-500">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-sm">No orders found.</p>
            </div>
        @else

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-slate-700/50">
                @foreach($orders as $order)
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
                    <a href="{{ route('orders.show', $order) }}" class="block px-4 py-3 hover:bg-slate-700/30 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-mono text-xs text-indigo-400 font-semibold">{{ $order->reference }}</span>
                            <span class="inline-flex text-xs font-medium px-2 py-0.5 rounded-full {{ $statusColor }}">{{ ucfirst($order->status) }}</span>
                        </div>
                        <div class="flex items-center justify-between mt-0.5">
                            <p class="text-sm text-white">{{ $order->customer_name }}</p>
                            <p class="text-sm font-semibold text-white">R{{ number_format($order->total, 2) }}</p>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $order->created_at->format('d M, H:i') }}</p>
                    </a>
                @endforeach
            </div>

            {{-- Desktop table --}}
            <table class="hidden sm:table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-left text-xs text-slate-400">
                        <th class="px-5 py-3 font-medium">Reference</th>
                        <th class="px-5 py-3 font-medium">Customer</th>
                        <th class="px-5 py-3 font-medium">Items</th>
                        <th class="px-5 py-3 font-medium">Total</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Payment</th>
                        <th class="px-5 py-3 font-medium">Date</th>
                        <th class="px-5 py-3 font-medium w-16"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach($orders as $order)
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
                        <tr class="hover:bg-slate-700/30 transition-colors">
                            <td class="px-5 py-3.5"><span class="font-mono text-xs text-indigo-400 font-semibold">{{ $order->reference }}</span></td>
                            <td class="px-5 py-3.5">
                                <p class="text-white font-medium">{{ $order->customer_name }}</p>
                                <p class="text-xs text-slate-400">{{ $order->customer_email }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-slate-300">{{ $order->items->count() }}</td>
                            <td class="px-5 py-3.5 font-semibold text-white">R{{ number_format($order->total, 2) }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex text-xs font-medium px-2 py-0.5 rounded-full {{ $statusColor }}">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="text-xs {{ $order->payment_status === 'paid' ? 'text-emerald-400' : 'text-amber-400' }}">{{ ucfirst($order->payment_status) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-xs text-slate-400">{{ $order->created_at->format('d M, H:i') }}</td>
                            <td class="px-5 py-3.5">
                                <a href="{{ route('orders.show', $order) }}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium transition-colors">View →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="px-5 py-4 border-t border-slate-700">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
