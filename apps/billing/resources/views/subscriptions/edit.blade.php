<x-app-layout>

    <x-slot name="header">
        <h2 class="text-2xl font-bold text-gray-900">Edit Subscription</h2>
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

        <form method="POST" action="{{ route('subscriptions.update', $subscription) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 uppercase font-semibold mb-2">Status</div>
                <select name="status" required
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                    <option value="active" {{ $subscription->status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="paused" {{ $subscription->status === 'paused' ? 'selected' : '' }}>Paused</option>
                    <option value="cancelled" {{ $subscription->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('status')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 uppercase font-semibold mb-2">Next Invoice Date</div>
                <input type="date" name="next_invoice_date" required
                       value="{{ $subscription->next_invoice_date->format('Y-m-d') }}"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-black focus:border-black">
                @error('next_invoice_date')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="auto_renew" value="1" {{ $subscription->auto_renew ? 'checked' : '' }}
                           class="rounded">
                    <span class="text-sm font-semibold text-gray-900">Auto-renew subscription</span>
                </label>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('subscriptions.show', $subscription) }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-900 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 font-semibold">
                    Save Changes
                </button>
            </div>

        </form>

    </div>

</x-app-layout>
