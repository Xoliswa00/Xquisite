@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'grid';
    $items = collect($content['items'] ?? []);
@endphp

<section class="bg-[var(--site-bg)] py-20">
    <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
        @if($content['heading'] ?? null)
            <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
        @endif
        @if($content['subtext'] ?? null)
            <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
        @endif

        @if($items->isNotEmpty())
            @if($variant === 'circles')
                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($items as $member)
                        <div>
                            @if($member['photo_url'] ?? null)
                                <img src="{{ $member['photo_url'] }}" alt="{{ $member['name'] ?? '' }}" class="mx-auto h-28 w-28 rounded-full border border-[var(--site-border)] object-cover">
                            @endif
                            <h4 class="mt-4 text-sm font-semibold text-[var(--site-text)]">{{ $member['name'] ?? '' }}</h4>
                            @if($member['role'] ?? null)
                                <span class="text-xs text-[var(--site-text-faint)]">{{ $member['role'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach($items as $member)
                        <div class="overflow-hidden rounded-xl border border-[var(--site-border)] bg-[var(--site-surface)]">
                            @if($member['photo_url'] ?? null)
                                <img src="{{ $member['photo_url'] }}" alt="{{ $member['name'] ?? '' }}" class="h-56 w-full object-cover">
                            @endif
                            <div class="p-4">
                                <h3 class="font-semibold text-[var(--site-text)]">{{ $member['name'] ?? '' }}</h3>
                                @if($member['role'] ?? null)
                                    <span class="text-sm text-[var(--site-text-faint)]">{{ $member['role'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</section>
