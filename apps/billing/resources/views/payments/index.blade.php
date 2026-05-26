<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Payments</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto px-4">

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Received</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">R {{ number_format($stats['total'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold">This Month</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">R {{ number_format($stats['this_month'], 2) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs text-gray-500 uppercase font-semibold">Transactions</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['count'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Date</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Invoice</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Client</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Method</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600">Reference</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600">Amount</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">{{ $payment->payment_date->format('d M Y') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-indigo-600 hover:underline">
                                    {{ $payment->invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">{{ $payment->invoice->client->name ?? '-' }}</td>
                            <td class="px-4 py-3 capitalize">{{ $payment->method }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $payment->reference ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium">R {{ number_format($payment->amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('payments.destroy', $payment) }}" method="POST"
                                      onsubmit="return confirm('Delete this payment record?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400 hover:text-red-600 text-xs">Del</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">No payments recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($payments->hasPages())
                <div class="px-4 py-3 border-t">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
