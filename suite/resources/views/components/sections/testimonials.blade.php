@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'carousel';
    $items = collect($content['items'] ?? []);
@endphp

<section class="bg-[var(--site-surface)] py-20">
    <div class="mx-auto {{ $variant === 'grid' ? 'max-w-6xl' : 'max-w-3xl' }} px-4 text-center sm:px-6">
        @if($content['heading'] ?? null)
            <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
        @endif
        @if($content['subtext'] ?? null)
            <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
        @endif

        @if($items->isEmpty())
            {{-- nothing to show --}}
        @elseif($variant === 'grid')
            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach($items as $item)
                    <div class="rounded-xl border border-[var(--site-border)] bg-[var(--site-bg)] p-6 text-left">
                        <blockquote class="text-sm italic text-[var(--site-text-muted)]">{{ $item['quote'] ?? '' }}</blockquote>
                        <div class="mt-5 flex items-center gap-3">
                            @if($item['avatar_url'] ?? null)
                                <img src="{{ $item['avatar_url'] }}" alt="{{ $item['name'] ?? '' }}" class="h-12 w-12 rounded-full object-cover">
                            @else
                                <span class="flex h-12 w-12 items-center justify-center rounded-full bg-[var(--tenant-primary)]/10 text-sm font-semibold text-[var(--tenant-primary)]">{{ $item['initials'] ?? Str::substr($item['name'] ?? '?', 0, 1) }}</span>
                            @endif
                            <div>
                                <h5 class="text-sm font-semibold text-[var(--site-text)]">{{ $item['name'] ?? '' }}</h5>
                                @if($item['role'] ?? null)
                                    <h6 class="text-xs text-[var(--site-text-faint)]">{{ $item['role'] }}</h6>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-12" x-data="{ i: 0, quotes: {{ \Illuminate\Support\Js::from($items->all()) }} }">
                <template x-for="(q, idx) in quotes" :key="idx">
                    <blockquote x-show="i === idx" x-transition.opacity class="flex flex-col items-center gap-4">
                        <template x-if="q.avatar_url">
                            <img :src="q.avatar_url" class="h-16 w-16 rounded-full object-cover" alt="">
                        </template>
                        <template x-if="!q.avatar_url">
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-[var(--tenant-primary)]/10 text-lg font-semibold text-[var(--tenant-primary)]" x-text="q.initials || (q.name ? q.name[0] : '?')"></span>
                        </template>
                        <p class="max-w-xl text-lg italic text-[var(--site-text)]" x-text="q.quote"></p>
                        <cite class="not-italic text-sm text-[var(--site-text-faint)]" x-text="q.name"></cite>
                    </blockquote>
                </template>
                <div class="mt-8 flex justify-center gap-2">
                    <template x-for="(q, idx) in quotes" :key="idx">
                        <button @click="i = idx" class="h-2.5 w-2.5 rounded-full transition" :class="i === idx ? 'bg-[var(--tenant-accent)]' : 'bg-gray-300'"></button>
                    </template>
                </div>
            </div>
        @endif
    </div>
</section>
