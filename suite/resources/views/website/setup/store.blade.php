@php
    $hasStore = $tenant->hasModule('pos');
    $pendingRequest = $tenant->moduleRequests()->where('module', 'pos')->where('status', 'pending')->exists();
@endphp
<x-app-layout>
    <x-slot name="header">Website Setup</x-slot>

    <div class="max-w-2xl space-y-6">

        <x-website-setup-progress :steps="$steps" :completed="$completed" :current-step="$step" :done-count="$doneCount" :total-count="$totalCount" />

        @if (session('success')) <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div> @endif
        @if ($errors->any())
            <div class="p-3 bg-red-500/10 border border-red-500/30 text-red-400 rounded-lg text-sm space-y-1">
                @foreach ($errors->all() as $error) <p>{{ $error }}</p> @endforeach
            </div>
        @endif

        <div class="bg-panel-2 rounded-xl border border-line p-6 space-y-5">
            <div>
                <h2 class="text-lg font-semibold text-ink">Sell products online</h2>
                <p class="text-sm text-ink-muted mt-1">Add a storefront to your site so customers can order and pay.</p>
            </div>

            @if (! $hasStore && ! $pendingRequest)
                <form method="POST" action="{{ route('settings.modules.request') }}">
                    @csrf
                    <input type="hidden" name="module" value="pos">
                    <button type="submit" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                        Enable Store
                    </button>
                </form>
            @elseif ($pendingRequest)
                <div class="px-4 py-3 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm">
                    Requested — you'll be notified once it's approved. You can continue setting up the rest of your site in the meantime.
                </div>
            @else
                <div class="space-y-3 max-w-sm">
                    <p class="text-sm font-medium text-ink">Add your first product</p>
                    <form method="POST" action="{{ route('products.store') }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="from_wizard" value="1">
                        <input type="hidden" name="stock_quantity" value="0">
                        <input type="text" name="name" required placeholder="Product name *"
                               class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <input type="number" name="price" step="0.01" min="0" required placeholder="Price (R) *"
                               class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <button type="submit" class="w-full px-4 py-2 bg-panel hover:bg-line border border-line-2 text-ink text-sm rounded-lg transition-colors">Add Product</button>
                    </form>
                </div>
            @endif
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
