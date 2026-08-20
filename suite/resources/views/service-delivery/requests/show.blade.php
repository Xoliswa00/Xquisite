<x-app-layout>
    @php
        $categoryLabels = ['software_solutions' => 'Software Solutions', 'business_automation' => 'Business Automation', 'data_intelligence' => 'Data & Intelligence', 'digital_solutions' => 'Digital Solutions', 'ongoing_support' => 'Ongoing Support', 'other' => 'Other'];
    @endphp

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">{{ $serviceRequest->name }}</h2>
            <a href="{{ route('service-requests.index') }}" class="text-sm text-ink-muted hover:text-ink">&larr; Requests</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-panel-2 rounded-xl p-6 space-y-3">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs text-ink-faint uppercase font-semibold">Contact</p>
                    <p class="text-ink">{{ $serviceRequest->email }}</p>
                    @if($serviceRequest->phone)<p class="text-ink-muted">{{ $serviceRequest->phone }}</p>@endif
                    @if($serviceRequest->company)<p class="text-ink-muted">{{ $serviceRequest->company }}</p>@endif
                </div>
                <div>
                    <p class="text-xs text-ink-faint uppercase font-semibold">Category</p>
                    <p class="text-ink">{{ $categoryLabels[$serviceRequest->category] ?? $serviceRequest->category }}</p>
                    @if($serviceRequest->budget_range)<p class="text-ink-muted text-xs mt-1">Budget: {{ $serviceRequest->budget_range }}</p>@endif
                    @if($serviceRequest->timeline)<p class="text-ink-muted text-xs">Timeline: {{ $serviceRequest->timeline }}</p>@endif
                </div>
            </div>

            <div class="pt-3 border-t border-line-2">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-1">What they need</p>
                <p class="text-sm text-ink whitespace-pre-line">{{ $serviceRequest->description }}</p>
            </div>

            <p class="text-xs text-ink-faint pt-2 border-t border-line-2">Submitted {{ $serviceRequest->created_at->format('d M Y, H:i') }}
                @if($serviceRequest->ip_address) · {{ $serviceRequest->ip_address }} @endif
            </p>
        </div>

        @if($serviceRequest->status === 'converted')
            <div class="bg-emerald-900/20 border border-emerald-800 rounded-xl p-5 text-sm text-emerald-300">
                Converted to client <strong>{{ $serviceRequest->convertedClient?->name }}</strong>
                @if($serviceRequest->convertedGig)
                    — <a href="{{ route('gigs.show', $serviceRequest->convertedGig) }}" class="underline">view the gig</a>
                @else
                    — <a href="{{ route('service-agreements.create') }}" class="underline">set up their service agreement</a>
                @endif
            </div>
        @elseif($serviceRequest->status === 'dismissed')
            <div class="bg-panel-2 rounded-xl p-5 text-sm text-ink-faint">Dismissed.</div>
        @else
            <div class="bg-panel-2 rounded-xl p-6 flex gap-3">
                <form method="POST" action="{{ route('service-requests.convert', $serviceRequest) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm rounded-lg font-medium">
                        {{ $serviceRequest->category === 'ongoing_support' ? 'Convert to Client' : 'Convert to Client + Gig' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('service-requests.dismiss', $serviceRequest) }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-panel hover:bg-line-2 text-ink-muted text-sm rounded-lg border border-line-2">Dismiss</button>
                </form>
            </div>
        @endif

    </div>
</x-app-layout>
