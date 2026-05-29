<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Subscription — {{ $subscription->product->name }}</h2>
            <span class="px-3 py-1 rounded text-sm font-semibold
                @if($subscription->status === 'active') bg-green-100 text-green-700
                @elseif($subscription->status === 'paused') bg-yellow-100 text-yellow-700
                @else bg-red-100 text-red-700
                @endif">
                {{ ucfirst($subscription->status) }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto p-6">

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-green-300 bg-green-50 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-6 mb-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 uppercase font-semibold mb-1">Client</div>
                <div class="text-lg font-semibold text-gray-900">{{ $subscription->client->name }}</div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 uppercase font-semibold mb-1">Product</div>
                <div class="text-lg font-semibold text-gray-900">{{ $subscription->product->name }}</div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 uppercase font-semibold mb-1">Frequency</div>
                <div class="text-lg font-semibold text-gray-900">{{ ucfirst($subscription->frequency) }}</div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 uppercase font-semibold mb-1">Next Invoice</div>
                <div class="text-lg font-semibold text-gray-900">{{ $subscription->next_invoice_date->format('d M Y') }}</div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 uppercase font-semibold mb-1">Started</div>
                <div class="text-lg font-semibold text-gray-900">{{ $subscription->start_date->format('d M Y') }}</div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 uppercase font-semibold mb-1">Auto-Renew</div>
                <div class="text-lg font-semibold text-gray-900">
                    @if($subscription->auto_renew)
                        <span class="text-green-600">Yes</span>
                    @else
                        <span class="text-gray-600">No</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('subscriptions.edit', $subscription) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Edit
            </a>

            @if($subscription->status === 'active')
                <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="paused">
                    <input type="hidden" name="next_invoice_date" value="{{ $subscription->next_invoice_date->format('Y-m-d') }}">
                    <input type="hidden" name="auto_renew" value="{{ $subscription->auto_renew ? '1' : '0' }}">
                    <button class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                        Pause
                    </button>
                </form>
            @elseif($subscription->status === 'paused')
                <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" class="inline">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="active">
                    <input type="hidden" name="next_invoice_date" value="{{ $subscription->next_invoice_date->format('Y-m-d') }}">
                    <input type="hidden" name="auto_renew" value="{{ $subscription->auto_renew ? '1' : '0' }}">
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Resume
                    </button>
                </form>
            @endif

            <form method="POST" action="{{ route('subscriptions.destroy', $subscription) }}" class="inline" onsubmit="return confirm('Cancel this subscription?');">
                @csrf
                @method('DELETE')
                <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Cancel
                </button>
            </form>
        </div>

    </div>

</x-app-layout>
