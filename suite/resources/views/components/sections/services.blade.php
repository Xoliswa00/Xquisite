@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'cards';
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
            @if($variant === 'list')
                <div class="mt-12 grid gap-6 text-left sm:grid-cols-2">
                    @foreach($items as $item)
                        <div class="flex items-start gap-4">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[var(--tenant-primary)]/10 text-lg text-[var(--tenant-primary)]">
                                <i class="fa {{ $item['icon'] ?? 'fa-star' }}"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-[var(--site-text)]">{{ $item['title'] ?? '' }}</h3>
                                <p class="mt-1 text-sm text-[var(--site-text-muted)]">{{ $item['text'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($items as $item)
                        <div class="rounded-xl border border-[var(--site-border)] bg-[var(--site-surface)] p-6 text-left transition hover:border-[var(--tenant-primary)]/50 hover:shadow-sm">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--tenant-primary)]/10 text-xl text-[var(--tenant-primary)]">
                                <i class="fa {{ $item['icon'] ?? 'fa-star' }}"></i>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-[var(--site-text)]">{{ $item['title'] ?? '' }}</h3>
                            <p class="mt-2 text-sm text-[var(--site-text-muted)]">{{ $item['text'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</section>
