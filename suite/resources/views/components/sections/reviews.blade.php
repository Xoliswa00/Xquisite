@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'cards';
    $items = collect($content['items'] ?? []);
@endphp

<section class="bg-[var(--site-surface)] py-20">
    <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
        @if($content['heading'] ?? null)
            <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
        @endif
        @if($content['subtext'] ?? null)
            <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
        @endif

        @if($items->isNotEmpty())
            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach($items as $item)
                    @php $rating = (int) ($item['rating'] ?? 5); @endphp
                    <div class="rounded-xl border border-[var(--site-border)] bg-[var(--site-bg)] p-6 text-left">
                        <div class="text-amber-400 text-sm">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa {{ $i <= $rating ? 'fa-star' : 'fa-star-o' }}"></i>
                            @endfor
                        </div>
                        <p class="mt-3 text-sm text-[var(--site-text-muted)]">{{ $item['body'] ?? '' }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-sm font-semibold text-[var(--site-text)]">{{ $item['author_name'] ?? '' }}</span>
                            @if($item['source'] ?? null)
                                <span class="text-xs text-[var(--site-text-faint)]">via {{ $item['source'] }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
