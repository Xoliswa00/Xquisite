@php
    $hasBooking = $tenant->hasModule('booking');
    $pendingRequest = $tenant->moduleRequests()->where('module', 'booking')->where('status', 'pending')->exists();
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
                <h2 class="text-lg font-semibold text-ink">Take bookings online</h2>
                <p class="text-sm text-ink-muted mt-1">Let customers book appointments straight from your website.</p>
            </div>

            @if (! $hasBooking && ! $pendingRequest)
                <form method="POST" action="{{ route('settings.modules.request') }}">
                    @csrf
                    <input type="hidden" name="module" value="booking">
                    <button type="submit" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-lg transition-colors">
                        Enable Bookings
                    </button>
                </form>
            @elseif ($pendingRequest)
                <div class="px-4 py-3 rounded-lg bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm">
                    Requested — you'll be notified once it's approved. You can continue setting up the rest of your site in the meantime.
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="space-y-3">
                        <p class="text-sm font-medium text-ink">Add your first service</p>
                        <form method="POST" action="{{ route('services.store') }}" class="space-y-2">
                            @csrf
                            <input type="hidden" name="from_wizard" value="1">
                            <input type="text" name="name" required placeholder="Service name *"
                                   class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            <div class="flex gap-2">
                                <input type="number" name="duration_minutes" required min="5" placeholder="Minutes *"
                                       class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                <input type="number" name="price" step="0.01" min="0" placeholder="Price (R)"
                                       class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            </div>
                            <button type="submit" class="w-full px-4 py-2 bg-panel hover:bg-line border border-line-2 text-ink text-sm rounded-lg transition-colors">Add Service</button>
                        </form>
                    </div>
                    <div class="space-y-3">
                        <p class="text-sm font-medium text-ink">Add a staff member</p>
                        <form method="POST" action="{{ route('staff.store') }}" class="space-y-2">
                            @csrf
                            <input type="hidden" name="from_wizard" value="1">
                            <input type="text" name="name" required placeholder="Staff name *"
                                   class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            <input type="text" name="role" placeholder="Role (optional)"
                                   class="w-full bg-panel border border-line-2 text-ink rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            <button type="submit" class="w-full px-4 py-2 bg-panel hover:bg-line border border-line-2 text-ink text-sm rounded-lg transition-colors">Add Staff</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        @include('website.setup._nav')
    </div>
</x-app-layout>
