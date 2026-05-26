<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Payment Detail</h2>
            <a href="{{ route('payments.index') }}" class="text-sm text-gray-500 hover:underline">Back to Payments</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">
        <div class="bg-white rounded-xl shadow p-6 space-y-4">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Invoice</dt>
                    <dd class="font-medium">
                        <a href="{{ route('invoices.show', $payment->invoice) }}" class="text-indigo-600 hover:underline">
                            {{ $payment->invoice->invoice_number }}
                        </a>
                    </dd>
                </div>
                <div>
                    <dt class="text-gray-500">Client</dt>
                    <dd class="font-medium">{{ $payment->invoice->client->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Payment Date</dt>
                    <dd class="font-medium">{{ $payment->payment_date->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Method</dt>
                    <dd class="font-medium capitalize">{{ $payment->method }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Reference</dt>
                    <dd class="font-medium">{{ $payment->reference ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Amount</dt>
                    <dd class="font-bold text-xl">R {{ number_format($payment->amount, 2) }}</dd>
                </div>
            </dl>

            <div class="pt-4 border-t flex justify-end">
                <form action="{{ route('payments.destroy', $payment) }}" method="POST"
                      onsubmit="return confirm('Delete this payment?')">
                    @csrf @method('DELETE')
                    <button class="px-4 py-2 bg-red-600 text-white rounded text-sm hover:bg-red-700">Delete Payment</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
