<x-app-layout>

    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">New Subscription</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6">

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-300 bg-red-50 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <h3 class="font-bold text-red-800">Error</h3>
                </div>
                <ul class="space-y-1 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('subscriptions.store') }}" class="space-y-6">
            @csrf

            <div class="bg-white shadow-sm rounded-lg p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Client</label>
                <select name="client_id" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                    <option value="">Select Client</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                            {{ $client->name }}
                        </option>
                    @endforeach
                </select>
                @error('client_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Product (Recurring)</label>
                <select name="product_id" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
                @error('product_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Start Date</label>
                <input type="date" name="start_date" required
                       value="{{ old('start_date', now()->addDay()->format('Y-m-d')) }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                @error('start_date')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Frequency</label>
                <select name="frequency" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                    <option value="">Select Frequency</option>
                    <option value="monthly" {{ old('frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="quarterly" {{ old('frequency') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                    <option value="yearly" {{ old('frequency') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
                @error('frequency')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="auto_renew" value="1" {{ old('auto_renew') ? 'checked' : '' }}
                           class="rounded">
                    <span class="text-sm font-semibold text-gray-900">Auto-renew this subscription</span>
                </label>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('subscriptions.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-900 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 font-semibold">
                    Create Subscription
                </button>
            </div>

        </form>

    </div>

</x-app-layout>
