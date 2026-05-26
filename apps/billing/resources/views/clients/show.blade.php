<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">{{ $client->name }}</h2>
            <div class="flex gap-2">
                <a href="{{ route('clients.edit', $client) }}"
                   class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Edit</a>
                <form action="{{ route('clients.destroy', $client) }}" method="POST"
                      onsubmit="return confirm('Delete this client?')">
                    @csrf @method('DELETE')
                    <button class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto px-4 space-y-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Client Details</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Name</dt><dd class="font-medium">{{ $client->name }}</dd></div>
                <div><dt class="text-gray-500">Contact Person</dt><dd class="font-medium">{{ $client->contact_person ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Email</dt><dd class="font-medium">{{ $client->email ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt><dd class="font-medium">{{ $client->phone ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">VAT Number</dt><dd class="font-medium">{{ $client->vat_number ?? '-' }}</dd></div>
                <div><dt class="text-gray-500">Since</dt><dd class="font-medium">{{ $client->created_at->format('d M Y') }}</dd></div>
                @if($client->billing_address)
                    <div class="col-span-2"><dt class="text-gray-500">Billing Address</dt><dd class="font-medium">{{ $client->billing_address }}</dd></div>
                @endif
            </dl>
        </div>

        <div class="grid grid-cols-2 gap-6">
            {{-- Recent Invoices --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-700">Invoices</h3>
                    <a href="{{ route('invoices.create') }}" class="text-xs text-indigo-600 hover:underline">+ New</a>
                </div>
                @forelse($client->invoices->take(5) as $inv)
                    <div class="flex justify-between items-center py-2 border-b last:border-0 text-sm">
                        <a href="{{ route('invoices.show', $inv) }}" class="text-indigo-600 hover:underline">
                            {{ $inv->invoice_number }}
                        </a>
                        <span class="px-2 py-0.5 rounded text-xs
                            @if($inv->status === 'paid') bg-green-100 text-green-700
                            @elseif($inv->status === 'overdue') bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-600 @endif">
                            {{ ucfirst($inv->status) }}
                        </span>
                        <span class="text-gray-700 font-medium">R {{ number_format($inv->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No invoices yet.</p>
                @endforelse
            </div>

            {{-- Recent Quotes --}}
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-700">Quotes</h3>
                    <a href="{{ route('quotes.create') }}" class="text-xs text-indigo-600 hover:underline">+ New</a>
                </div>
                @forelse($client->quotes->take(5) as $q)
                    <div class="flex justify-between items-center py-2 border-b last:border-0 text-sm">
                        <a href="{{ route('quotes.show', $q) }}" class="text-indigo-600 hover:underline">
                            {{ $q->quote_number }}
                        </a>
                        <span class="px-2 py-0.5 rounded text-xs bg-gray-100 text-gray-600">{{ ucfirst($q->status) }}</span>
                        <span class="text-gray-700 font-medium">R {{ number_format($q->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No quotes yet.</p>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
