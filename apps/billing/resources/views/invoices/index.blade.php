<x-app-layout>

    <div class="bg-white shadow-sm sm:rounded-md p-3 mb-3">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Invoices</h2>
            <a href="{{ route('invoices.create') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded text-sm">
                + New Invoice
            </a>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100 text-left uppercase text-xs">
                            <tr>
                                <th class="p-3">Invoice #</th>
                                <th class="p-3">Client</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Total</th>
                                <th class="p-3">Due Date</th>
                                <th class="p-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr class="border-t hover:bg-gray-50">
                                    <td class="p-3 font-semibold">
                                        <a href="{{ route('invoices.show', $invoice->id) }}"
                                           class="text-blue-600 hover:underline">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="p-3">{{ $invoice->client->name ?? 'N/A' }}</td>
                                    <td class="p-3">
                                        @php
                                            $colors = [
                                                'draft'   => 'bg-gray-200 text-gray-700',
                                                'sent'    => 'bg-blue-100 text-blue-700',
                                                'paid'    => 'bg-green-100 text-green-700',
                                                'overdue' => 'bg-red-100 text-red-700',
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 rounded text-xs {{ $colors[$invoice->status] ?? 'bg-gray-200' }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right font-semibold">
                                        R {{ number_format($invoice->total, 2) }}
                                    </td>
                                    <td class="p-3 text-gray-600">
                                        {{ $invoice->due_date?->format('Y-m-d') ?? '-' }}
                                    </td>
                                    <td class="p-3 text-center">
                                        <a href="{{ route('invoices.show', $invoice->id) }}"
                                           class="text-blue-600 text-xs">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center p-6 text-gray-500">
                                        No invoices found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
