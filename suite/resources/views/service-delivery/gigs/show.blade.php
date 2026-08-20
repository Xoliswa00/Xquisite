<x-app-layout>
    @php
        $statusLabels = ['lead' => 'Lead', 'quoted' => 'Quoted', 'in_progress' => 'In Progress', 'review' => 'Review', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
        $categoryLabels = ['software_solutions' => 'Software Solutions', 'business_automation' => 'Business Automation', 'data_intelligence' => 'Data & Intelligence', 'digital_solutions' => 'Digital Solutions', 'other' => 'Other'];
    @endphp

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-bold text-[#D4AF37]">{{ $gig->title }}</h2>
                <span class="px-2 py-0.5 rounded text-xs font-medium bg-panel-2 text-ink-muted border border-line-2">{{ $statusLabels[$gig->status] }}</span>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('gigs.index') }}" class="text-sm text-ink-muted hover:text-ink self-center">&larr; Gigs</a>
                <a href="{{ route('gigs.edit', $gig) }}" class="px-3 py-2 bg-[#002B5B] hover:bg-[#0078D4] text-white text-sm rounded-lg">Edit</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-900/30 border border-emerald-700 text-emerald-300 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Client</p>
                <p class="text-ink font-medium">{{ $gig->client?->name ?? '—' }}</p>
                <p class="text-ink-faint text-xs mt-0.5">{{ $gig->client?->email }}</p>
            </div>
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Service Line</p>
                <p class="text-ink font-medium">{{ $categoryLabels[$gig->category] ?? $gig->category }}</p>
            </div>
            <div class="bg-panel-2 rounded-xl p-5">
                <p class="text-xs text-ink-faint uppercase font-semibold mb-2">Time Logged</p>
                <p class="text-ink font-medium">{{ number_format($gig->totalMinutesLogged() / 60, 1) }} hrs</p>
            </div>
        </div>

        {{-- Discovery --}}
        <div class="bg-panel-2 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-ink-muted mb-2">Discovery</h3>
            @if($gig->description)
                <p class="text-sm text-ink mb-3">{{ $gig->description }}</p>
            @endif
            @if($gig->discovery_notes)
                <p class="text-xs text-ink-faint uppercase font-semibold mb-1">Notes from the client conversation</p>
                <p class="text-sm text-ink-muted whitespace-pre-line">{{ $gig->discovery_notes }}</p>
            @else
                <p class="text-sm text-ink-faint">No discovery notes yet.</p>
            @endif
        </div>

        {{-- Status --}}
        <div class="bg-panel-2 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-ink-muted mb-3">Status</h3>
            <form method="POST" action="{{ route('gigs.status', $gig) }}" class="flex items-center gap-3">
                @csrf @method('PATCH')
                <select name="status" class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    @foreach($statusLabels as $key => $label)
                        <option value="{{ $key }}" @selected($gig->status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-panel hover:bg-line-2 text-ink text-sm rounded-lg border border-line-2">Update</button>
            </form>
        </div>

        {{-- Quotes --}}
        <div class="bg-panel-2 rounded-xl overflow-hidden">
            <div class="px-5 py-3 border-b border-line-2 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-ink-muted">Quotes</h3>
                <a href="{{ route('quotes.create', ['gig_id' => $gig->id]) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium">+ Build Quote</a>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-line-2">
                    @forelse($gig->quotes as $quote)
                        <tr class="hover:bg-line-2/40">
                            <td class="px-4 py-2.5 text-ink">{{ $quote->reference }} — {{ $quote->title }}</td>
                            <td class="px-4 py-2.5 text-ink-muted">R{{ number_format($quote->total, 2) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-xs font-medium
                                    @if($quote->status === 'accepted') bg-emerald-900/40 text-emerald-400
                                    @elseif($quote->status === 'sent') bg-yellow-900/40 text-yellow-400
                                    @elseif($quote->status === 'declined') bg-red-900/40 text-red-400
                                    @else bg-panel text-ink-muted @endif">
                                    {{ ucfirst($quote->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ route('quotes.show', $quote) }}" class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-ink-faint">No quotes yet — build one to price this gig.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Time entries --}}
        <div class="bg-panel-2 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-ink-muted mb-3">Log Time</h3>
            <form method="POST" action="{{ route('gigs.log-time', $gig) }}" class="flex items-end gap-3 mb-4">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs font-medium text-ink-faint mb-1">Description</label>
                    <input type="text" name="description" required class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                </div>
                <div class="w-24">
                    <label class="block text-xs font-medium text-ink-faint mb-1">Minutes</label>
                    <input type="number" name="minutes" min="1" required class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Date</label>
                    <input type="date" name="logged_at" value="{{ date('Y-m-d') }}" required class="bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                </div>
                <button type="submit" class="px-4 py-2 bg-panel hover:bg-line-2 text-ink text-sm rounded-lg border border-line-2">Log</button>
            </form>

            @if($gig->timeEntries->count())
                <ul class="space-y-1.5 pt-3 border-t border-line-2">
                    @foreach($gig->timeEntries as $entry)
                        <li class="text-xs text-ink-muted flex justify-between">
                            <span>{{ $entry->description }} <span class="text-ink-faint">({{ $entry->logged_at->format('d M Y') }})</span></span>
                            <span class="text-ink-faint">{{ $entry->minutes }} min</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</x-app-layout>
