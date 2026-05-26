<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="font-semibold text-xl">Invoice {{ $invoice->invoice_number }}</h2>
                <span class="px-3 py-1 rounded text-sm
                    @if($invoice->status === 'paid') bg-green-100 text-green-700
                    @elseif($invoice->status === 'sent') bg-blue-100 text-blue-700
                    @elseif($invoice->status === 'overdue') bg-red-100 text-red-700
                    @else bg-gray-200 text-gray-700
                    @endif">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('invoices.edit', $invoice) }}"
                   class="px-3 py-1.5 border border-gray-300 rounded text-sm hover:bg-gray-50">
                    Edit
                </a>
                <a href="{{ route('invoices.download', $invoice) }}"
                   class="px-3 py-1.5 bg-slate-900 text-white rounded text-sm hover:bg-slate-700">
                    Download PDF
                </a>
                @if($invoice->status === 'draft')
                    <form method="POST" action="{{ route('invoices.send', $invoice) }}" class="inline">
                        @csrf
                        <button class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                            Mark Sent
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded shadow p-6 mb-6">

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Client</p>
                    <p class="font-semibold">{{ $invoice->client->name ?? 'N/A' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase">Due Date</p>
                    <p class="font-semibold">{{ $invoice->due_date?->format('d M Y') ?? '-' }}</p>
                </div>
            </div>

            <table class="w-full border-collapse mb-6">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="text-left p-2 text-sm">Description</th>
                        <th class="text-right p-2 text-sm">Qty</th>
                        <th class="text-right p-2 text-sm">Unit Price</th>
                        <th class="text-right p-2 text-sm">VAT</th>
                        <th class="text-right p-2 text-sm">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->items as $item)
                        <tr class="border-b">
                            <td class="p-2">{{ $item->description }}</td>
                            <td class="p-2 text-right">{{ $item->quantity }}</td>
                            <td class="p-2 text-right">R {{ number_format($item->unit_price, 2) }}</td>
                            <td class="p-2 text-right">R {{ number_format($item->vat_amount, 2) }}</td>
                            <td class="p-2 text-right">R {{ number_format($item->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="text-right space-y-1">
                <div>Subtotal: <strong>R {{ number_format($invoice->total - $invoice->vat_total, 2) }}</strong></div>
                <div>VAT (15%): <strong>R {{ number_format($invoice->vat_total, 2) }}</strong></div>
                <div class="text-xl font-bold mt-2">Total: R {{ number_format($invoice->total, 2) }}</div>
            </div>

        </div>

        {{-- Payments recorded --}}
        @if($invoice->payments->count())
            <div class="bg-white rounded shadow p-6 mb-6">
                <h3 class="font-bold text-lg mb-3">Payments</h3>
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="text-left p-2">Date</th>
                            <th class="text-left p-2">Method</th>
                            <th class="text-left p-2">Reference</th>
                            <th class="text-right p-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->payments as $payment)
                            <tr class="border-b">
                                <td class="p-2">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="p-2">{{ ucfirst($payment->method) }}</td>
                                <td class="p-2">{{ $payment->reference ?? '-' }}</td>
                                <td class="p-2 text-right">R {{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="text-right mt-2 font-semibold">
                    Paid: R {{ number_format($invoice->payments->sum('amount'), 2) }}
                </div>
            </div>
        @endif

        {{-- Record payment (only if not fully paid) --}}
        @if($invoice->status !== 'paid')
            <div class="bg-white rounded shadow p-6">
                <h3 class="font-bold text-lg mb-3">Record Payment</h3>
                <form method="POST" action="{{ route('invoices.payments.store', $invoice->id) }}">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Amount</label>
                            <input type="number" name="amount" step="0.01" min="0.01"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                   value="{{ $invoice->total - $invoice->payments->sum('amount') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Method</label>
                            <select name="method" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="eft">EFT</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="debit_order">Debit Order</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Payment Date</label>
                            <input type="date" name="payment_date"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm"
                                   value="{{ now()->format('Y-m-d') }}">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reference</label>
                            <input type="text" name="reference"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                    </div>
                    <div class="mt-4">
                        <button type="submit"
                                class="bg-green-600 text-white px-4 py-2 rounded text-sm">
                            Record Payment
                        </button>
                    </div>
                </form>
            </div>
        @endif

    </div>

</x-app-layout>
