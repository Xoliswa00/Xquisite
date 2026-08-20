<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">Gigs</h2>
            <a href="{{ route('gigs.create') }}" class="px-4 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white text-sm rounded-lg font-medium">+ New Gig</a>
        </div>
    </x-slot>

    @php
        $statusLabels = ['lead' => 'Lead', 'quoted' => 'Quoted', 'in_progress' => 'In Progress', 'review' => 'Review', 'completed' => 'Completed'];
        $categoryLabels = ['software_solutions' => 'Software', 'business_automation' => 'Automation', 'data_intelligence' => 'Data & BI', 'digital_solutions' => 'Digital', 'other' => 'Other'];
    @endphp

    <div class="max-w-full mx-auto p-6">

        @if(session('success'))
            <div class="mb-4 p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 overflow-x-auto">
            @foreach($statuses as $status)
                <div class="bg-panel-2 rounded-xl min-w-[220px]">
                    <div class="px-4 py-3 border-b border-line-2 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-ink">{{ $statusLabels[$status] }}</h3>
                        <span class="text-xs text-ink-faint">{{ $gigs->get($status, collect())->count() }}</span>
                    </div>
                    <div class="p-3 space-y-3">
                        @forelse($gigs->get($status, collect()) as $gig)
                            <div class="bg-panel rounded-lg p-3 border border-line-2">
                                <a href="{{ route('gigs.show', $gig) }}" class="text-sm font-medium text-ink hover:text-[#0078D4] block">{{ $gig->title }}</a>
                                <p class="text-xs text-ink-faint mt-1">{{ $gig->client?->name ?? '—' }}</p>
                                <p class="text-xs text-ink-faint mt-0.5">{{ $categoryLabels[$gig->category] ?? $gig->category }}</p>
                                @if($quote = $gig->quotes->sortByDesc('id')->first())
                                    <p class="text-xs text-[#0078D4] font-medium mt-1">R{{ number_format($quote->total, 2) }}</p>
                                @endif

                                @php
                                    $next = match($status) { 'lead' => 'quoted', 'quoted' => 'in_progress', 'in_progress' => 'review', 'review' => 'completed', default => null };
                                @endphp
                                @if($next)
                                    <form method="POST" action="{{ route('gigs.status', $gig) }}" class="mt-2">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $next }}">
                                        <button type="submit" class="text-xs text-ink-muted hover:text-[#0078D4]">Move to {{ $statusLabels[$next] }} &rarr;</button>
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-xs text-ink-faint text-center py-4">No gigs</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
