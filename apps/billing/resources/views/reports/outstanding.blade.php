<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Outstanding Invoices</h2>
            <a href="{{ route('reports.index') }}" class="text-sm text-gray-500 hover:underline">Back to Reports</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        @php
            $total = $invoices->sum('total');
            $overdue = $invoices->where('status', 'overdue')->sum('total');
        @endphp

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold">Outstanding Total</p>
                <p class="text-2xl font-bold text-yellow-600 mt-1">R {{ number_format($total, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold">Overdue</p>
                <p class="text-2xl font-bold text-red-600 mt-1">R {{ number_format($overdue, 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold">Invoices</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $invoices->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Invoice</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Client</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Due Date</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50 {{ $invoice->status === 'overdue' ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-indigo-600 hover:underline font-medium">
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ $invoice->client->name ?? '-' }}</td>
                            <td class="px-4 py-3 {{ $invoice->due_date && $invoice->due_date->isPast() ? 'text-red-600 font-medium' : '' }}">
                                {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                                @if($invoice->due_date && $invoice->due_date->isPast())
                                    <span class="text-xs">({{ $invoice->due_date->diffForHumans() }})</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($invoice->status === 'overdue') bg-red-100 text-red-700
                                    @elseif($invoice->status === 'sent') bg-blue-100 text-blue-700
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-medium">R {{ number_format($invoice->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400">No outstanding invoices.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
