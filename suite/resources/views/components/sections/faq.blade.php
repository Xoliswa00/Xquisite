@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'accordion';
    $items = collect($content['items'] ?? []);
@endphp

<section class="bg-[var(--site-surface)] py-20">
    <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
        @if($content['heading'] ?? null)
            <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
        @endif
        @if($content['subtext'] ?? null)
            <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
        @endif

        @if($items->isNotEmpty())
            @if($variant === 'two-column')
                <div class="mt-12 grid gap-8 text-left sm:grid-cols-2">
                    @foreach($items as $item)
                        <div>
                            <h3 class="font-semibold text-[var(--site-text)]">{{ $item['question'] ?? '' }}</h3>
                            <p class="mt-2 text-sm text-[var(--site-text-muted)]">{{ $item['answer'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-12 space-y-3 text-left" x-data="{ open: null }">
                    @foreach($items as $i => $item)
                        <div class="rounded-xl border border-[var(--site-border)] bg-[var(--site-bg)]">
                            <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left">
                                <span class="font-medium text-[var(--site-text)]">{{ $item['question'] ?? '' }}</span>
                                <i class="fa fa-chevron-down text-xs text-[var(--site-text-faint)] transition" :class="open === {{ $i }} ? 'rotate-180' : ''"></i>
                            </button>
                            <div x-cloak x-show="open === {{ $i }}" x-transition>
                                <p class="px-5 pb-4 text-sm text-[var(--site-text-muted)]">{{ $item['answer'] ?? '' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    </div>
</section>
