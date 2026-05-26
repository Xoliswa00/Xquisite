<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Revenue Report — {{ $year }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('reports.revenue', ['year' => $year - 1]) }}"
                   class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50">&larr; {{ $year - 1 }}</a>
                <a href="{{ route('reports.revenue', ['year' => $year + 1]) }}"
                   class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded hover:bg-gray-50">{{ $year + 1 }} &rarr;</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4">
        @php $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; @endphp

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-6 py-3 font-semibold text-gray-600">Month</th>
                        <th class="text-right px-6 py-3 font-semibold text-gray-600">Invoiced</th>
                        <th class="text-right px-6 py-3 font-semibold text-gray-600">Received</th>
                        <th class="text-right px-6 py-3 font-semibold text-gray-600">Collection Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php $totalInvoiced = 0; $totalPaid = 0; @endphp
                    @foreach($months as $i => $month)
                        @php
                            $mNum = $i + 1;
                            $inv = $invoiced[$mNum]->total ?? 0;
                            $paid = $monthly[$mNum]->total ?? 0;
                            $rate = $inv > 0 ? round(($paid / $inv) * 100) : 0;
                            $totalInvoiced += $inv;
                            $totalPaid += $paid;
                        @endphp
                        <tr class="{{ ($inv == 0 && $paid == 0) ? 'text-gray-300' : '' }} hover:bg-gray-50">
                            <td class="px-6 py-3 font-medium">{{ $month }}</td>
                            <td class="px-6 py-3 text-right">{{ $inv > 0 ? 'R ' . number_format($inv, 2) : '—' }}</td>
                            <td class="px-6 py-3 text-right {{ $paid > 0 ? 'text-green-600 font-medium' : '' }}">
                                {{ $paid > 0 ? 'R ' . number_format($paid, 2) : '—' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                @if($inv > 0)
                                    <span class="px-2 py-0.5 rounded text-xs {{ $rate >= 80 ? 'bg-green-100 text-green-700' : ($rate >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ $rate }}%
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t font-semibold">
                    <tr>
                        <td class="px-6 py-3">Total</td>
                        <td class="px-6 py-3 text-right">R {{ number_format($totalInvoiced, 2) }}</td>
                        <td class="px-6 py-3 text-right text-green-700">R {{ number_format($totalPaid, 2) }}</td>
                        <td class="px-6 py-3 text-right">
                            @if($totalInvoiced > 0)
                                {{ round(($totalPaid / $totalInvoiced) * 100) }}%
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
