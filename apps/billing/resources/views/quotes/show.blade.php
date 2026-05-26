<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl">Quote {{ $quote->quote_number }}</h2>
            <span class="text-sm text-gray-500">{{ ucfirst($quote->status) }}</span>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded shadow p-6 mb-4">

            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Client</p>
                    <p class="font-semibold">{{ $quote->client->name ?? 'N/A' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase">Source</p>
                    <p class="font-semibold">{{ ucfirst($quote->source ?? '-') }}</p>
                </div>
            </div>

            <table class="w-full border-collapse">
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
                    @foreach($quote->items as $item)
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

            <div class="mt-6 text-right space-y-1">
                <div class="text-sm">Subtotal: <strong>R {{ number_format($quote->subtotal ?? 0, 2) }}</strong></div>
                <div class="text-sm">VAT (15%): <strong>R {{ number_format($quote->vat ?? 0, 2) }}</strong></div>
                <div class="text-xl font-bold mt-2">Total: R {{ number_format($quote->total ?? 0, 2) }}</div>
            </div>

        </div>

        {{-- Actions --}}
        <div class="flex gap-3 flex-wrap">

            <a href="{{ route('quotes.edit', $quote->id) }}"
               class="px-4 py-2 border border-gray-300 rounded text-sm hover:bg-gray-50">
                Edit
            </a>

            <a href="{{ route('quotes.download', $quote->id) }}"
               class="px-4 py-2 bg-slate-900 text-white rounded text-sm hover:bg-slate-700">
                Download PDF
            </a>

            @if($quote->status === 'draft' && $quote->source === 'internal')
                <form method="POST" action="{{ route('quotes.send', $quote->id) }}">
                    @csrf
                    <button class="bg-indigo-600 text-white px-4 py-2 rounded text-sm">Send to Client</button>
                </form>
            @endif

            @if($quote->status === 'draft' && $quote->source === 'client')
                <form method="POST" action="{{ route('quotes.submit', $quote->id) }}">
                    @csrf
                    <button class="bg-yellow-500 text-white px-4 py-2 rounded text-sm">Submit for Review</button>
                </form>
            @endif

            @if(in_array($quote->status, ['sent', 'submitted']))
                <form method="POST" action="{{ route('quotes.approve', $quote->id) }}">
                    @csrf
                    <button class="bg-green-600 text-white px-4 py-2 rounded text-sm">Approve</button>
                </form>
                <form method="POST" action="{{ route('quotes.reject', $quote->id) }}">
                    @csrf
                    <button class="bg-red-600 text-white px-4 py-2 rounded text-sm">Reject</button>
                </form>
            @endif

            @if(in_array($quote->status, ['approved', 'sent']))
                <form method="POST" action="{{ route('quotes.convert', $quote->id) }}">
                    @csrf
                    <button class="bg-emerald-600 text-white px-4 py-2 rounded text-sm font-semibold">
                        Convert to Invoice
                    </button>
                </form>
            @endif

        </div>

    </div>

</x-app-layout>