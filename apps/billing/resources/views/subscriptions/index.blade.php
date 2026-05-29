<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Subscriptions</h2>
            <a href="{{ route('subscriptions.create') }}"
               class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800">
                New Subscription
            </a>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto p-6">

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-300 bg-green-50 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($subscriptions->count())
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Client</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Product</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Frequency</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Next Invoice</th>
                            <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($subscriptions as $sub)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $sub->client->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $sub->product->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst($sub->frequency) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="px-2 py-1 rounded text-xs font-semibold
                                        @if($sub->status === 'active') bg-green-100 text-green-700
                                        @elseif($sub->status === 'paused') bg-yellow-100 text-yellow-700
                                        @else bg-red-100 text-red-700
                                        @endif">
                                        {{ ucfirst($sub->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $sub->next_invoice_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('subscriptions.show', $sub) }}"
                                       class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $subscriptions->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg p-12 text-center">
                <p class="text-gray-500 mb-4">No subscriptions yet</p>
                <a href="{{ route('subscriptions.create') }}"
                   class="inline-block px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800">
                    Create One
                </a>
            </div>
        @endif

    </div>

</x-app-layout>
