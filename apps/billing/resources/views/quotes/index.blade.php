<x-app-layout>

    <!-- HEADER SLOT -->
    <div class="bg-white shadow-sm sm:rounded-md p-3 mb-3">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Quotes
            </h2>

            <div class="flex gap-2">
                <a href="{{ route('quotes.create', ['mode' => 'internal']) }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded text-sm">
                    + Internal Quote
                </a>

                <a href="{{ route('quotes.create', ['mode' => 'client']) }}"
                   class="bg-green-600 text-white px-4 py-2 rounded text-sm">
                    + Client Quote
                </a>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">

                        <thead class="bg-gray-100 text-left uppercase text-xs">
                            <tr>
                                <th class="p-3">Quote #</th>
                                <th class="p-3">Client</th>
                                <th class="p-3">Source</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Total</th>
                                <th class="p-3">Date</th>
                                <th class="p-3 text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($quotes as $quote)
                                <tr class="border-t hover:bg-gray-50">

                                    <!-- QUOTE NUMBER -->
                                    <td class="p-3 font-semibold">
                                        <a href="{{ route('quotes.show', $quote->id) }}"
                                           class="text-blue-600 hover:underline">
                                            {{ $quote->quote_number }}
                                        </a>
                                    </td>

                                    <!-- CLIENT -->
                                    <td class="p-3">
                                        {{ $quote->client->name ?? 'N/A' }}
                                    </td>

                                    <!-- SOURCE -->
                                    <td class="p-3">
                                        @if($quote->source === 'client')
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs">
                                                Client
                                            </span>
                                        @else
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs">
                                                Internal
                                            </span>
                                        @endif
                                    </td>

                                    <!-- STATUS -->
                                    <td class="p-3">
                                        @php
                                            $colors = [
                                                'draft' => 'bg-gray-200 text-gray-700',
                                                'submitted' => 'bg-yellow-100 text-yellow-700',
                                                'under_review' => 'bg-purple-100 text-purple-700',
                                                'sent' => 'bg-blue-100 text-blue-700',
                                                'viewed' => 'bg-indigo-100 text-indigo-700',
                                                'approved' => 'bg-green-100 text-green-700',
                                                'rejected' => 'bg-red-100 text-red-700',
                                                'invoiced' => 'bg-emerald-100 text-emerald-700',
                                            ];
                                        @endphp

                                        <span class="px-2 py-1 rounded text-xs {{ $colors[$quote->status] ?? 'bg-gray-200' }}">
                                            {{ ucfirst(str_replace('_', ' ', $quote->status)) }}
                                        </span>
                                    </td>

                                    <!-- TOTAL -->
                                    <td class="p-3 text-right font-semibold">
                                        R {{ number_format($quote->total, 2) }}
                                    </td>

                                    <!-- DATE -->
                                    <td class="p-3 text-gray-600">
                                        {{ $quote->created_at->format('Y-m-d') }}
                                    </td>

                                    <!-- ACTIONS -->
                                    <td class="p-3 text-center">
                                        <div class="flex justify-center gap-2">

                                            <!-- VIEW -->
                                            <a href="{{ route('quotes.show', $quote->id) }}"
                                               class="text-blue-600 text-xs">
                                                View
                                            </a>

                                            <!-- INTERNAL SEND -->
                                            @if($quote->status === 'draft' && $quote->source === 'internal')
                                                <form method="POST" action="{{ route('quotes.send', $quote->id) }}">
                                                    @csrf
                                                    <button class="text-indigo-600 text-xs">
                                                        Send
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- CLIENT SUBMIT -->
                                            @if($quote->status === 'draft' && $quote->source === 'client')
                                                <form method="POST" action="{{ route('quotes.submit', $quote->id) }}">
                                                    @csrf
                                                    <button class="text-yellow-600 text-xs">
                                                        Submit
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- APPROVE / REJECT -->
                                            @if(in_array($quote->status, ['sent', 'submitted']))
                                                <form method="POST" action="{{ route('quotes.approve', $quote->id) }}">
                                                    @csrf
                                                    <button class="text-green-600 text-xs">
                                                        Approve
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('quotes.reject', $quote->id) }}">
                                                    @csrf
                                                    <button class="text-red-600 text-xs">
                                                        Reject
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- CONVERT TO INVOICE -->
                                            @if(in_array($quote->status, ['approved', 'sent']))
                                                <form method="POST" action="{{ route('quotes.convert', $quote->id) }}">
                                                    @csrf
                                                    <button class="text-emerald-600 text-xs font-semibold">
                                                        → Invoice
                                                    </button>
                                                </form>
                                            @endif

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center p-6 text-gray-500">
                                        No quotes found.
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