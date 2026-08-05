@props(['steps', 'completed', 'currentStep', 'doneCount', 'totalCount'])

@php
    $labels = [
        'welcome'       => 'Welcome',
        'business-type' => 'Business Type',
        'template'      => 'Template',
        'logo'          => 'Logo',
        'colours'       => 'Colours',
        'fonts'         => 'Fonts',
        'business-info' => 'Business Info',
        'contact'       => 'Contact',
        'social'        => 'Social',
        'booking'       => 'Booking',
        'store'         => 'Store',
        'domain'        => 'Domain',
        'preview'       => 'Preview',
        'publish'       => 'Publish',
    ];
@endphp

<div class="space-y-3">
    <div class="flex items-center justify-between">
        <p class="text-xs font-medium text-ink-muted">Setting up your website</p>
        <span class="text-xs font-semibold text-[#0078D4] bg-[#0078D4]/10 px-2.5 py-1 rounded-full border border-[#0078D4]/20">{{ $doneCount }}/{{ $totalCount }} done</span>
    </div>

    <div class="flex gap-1.5 overflow-x-auto pb-1">
        @foreach ($steps as $s)
            @php $isCurrent = $s === $currentStep; $isDone = $completed[$s] ?? false; @endphp
            <a href="{{ route('website.setup.show', ['step' => $s]) }}"
               class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium border transition-colors whitespace-nowrap
                   {{ $isCurrent ? 'bg-[#0078D4] border-[#0078D4] text-white' : ($isDone ? 'border-emerald-700/40 text-emerald-400 hover:border-emerald-600' : 'border-line-2 text-ink-faint hover:text-ink hover:border-ink-faint') }}">
                @if ($isDone && ! $isCurrent)
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                @endif
                {{ $labels[$s] ?? ucfirst($s) }}
            </a>
        @endforeach
    </div>
</div>
