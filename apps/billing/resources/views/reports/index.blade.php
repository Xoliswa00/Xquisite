<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Reports</h2>
            <div class="flex gap-2 text-sm">
                <a href="{{ route('reports.revenue') }}" class="px-3 py-1.5 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Revenue</a>
                <a href="{{ route('reports.outstanding') }}" class="px-3 py-1.5 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Outstanding</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-6xl mx-auto px-4 space-y-6">

        {{-- KPI Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                $cards = [
                    ['label' => 'Total Invoiced',   'value' => 'R ' . number_format($stats['total_invoiced'], 2),   'color' => 'text-gray-900'],
                    ['label' => 'Total Paid',        'value' => 'R ' . number_format($stats['total_paid'], 2),       'color' => 'text-green-600'],
                    ['label' => 'Outstanding',       'value' => 'R ' . number_format($stats['total_outstanding'], 2),'color' => 'text-yellow-600'],
                    ['label' => 'Overdue',           'value' => 'R ' . number_format($stats['total_overdue'], 2),    'color' => 'text-red-600'],
                ];
            @endphp
            @foreach($cards as $card)
                <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                    <p class="text-xs text-gray-500 uppercase font-semibold tracking-wide">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold {{ $card['color'] }} mt-2">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs text-gray-500 uppercase font-semibold">Invoices</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['invoice_count'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs text-gray-500 uppercase font-semibold">Clients</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['client_count'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <p class="text-xs text-gray-500 uppercase font-semibold">Quotes</p>
                <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['quote_count'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">

            {{-- Revenue by Month --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-700">Revenue (Last 12 Months)</h3>
                    <a href="{{ route('reports.revenue') }}" class="text-xs text-indigo-600 hover:underline">Full view</a>
                </div>
                @forelse($revenueByMonth as $row)
                    @php $months = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; @endphp
                    <div class="flex items-center justify-between py-2 border-b last:border-0 text-sm">
                        <span class="text-gray-600">{{ $months[$row->month] ?? $row->month }} {{ $row->year }}</span>
                        <span class="font-semibold text-gray-900">R {{ number_format($row->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">No payment data yet.</p>
                @endforelse
            </div>

            {{-- Top Clients --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Top Clients by Revenue</h3>
                @forelse($topClients as $i => $client)
                    <div class="flex items-center justify-between py-2 border-b last:border-0 text-sm">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-500 text-xs flex items-center justify-center font-bold">
                                {{ $i + 1 }}
                            </span>
                            <a href="{{ route('clients.show', $client) }}" class="text-indigo-600 hover:underline">
                                {{ $client->name }}
                            </a>
                        </div>
                        <span class="font-semibold text-gray-900">
                            R {{ number_format($client->invoices_sum_total ?? 0, 2) }}
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 py-4 text-center">No client data yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
