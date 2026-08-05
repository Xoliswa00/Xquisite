@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'row';
    $items = collect($content['items'] ?? []);
    $gridColsClass = match (min(4, max(1, $items->count()))) {
        1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3',
        default => 'lg:grid-cols-4',
    };
@endphp

@if($items->isNotEmpty())
    <section class="bg-[var(--site-surface)] py-20">
        <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
            @if($content['heading'] ?? null)
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
            @endif

            <div
                class="mt-12 grid grid-cols-2 gap-6 {{ $gridColsClass }}"
                x-data="{
                    stats: {{ \Illuminate\Support\Js::from($items->map(fn ($i) => ['label' => $i['label'] ?? '', 'suffix' => $i['suffix'] ?? '', 'target' => (int) ($i['value'] ?? 0), 'value' => 0])->all()) }},
                }"
                x-init="stats.forEach((s) => { const step = Math.max(1, Math.ceil(s.target / 60)); const t = setInterval(() => { s.value = Math.min(s.target, s.value + step); if (s.value >= s.target) clearInterval(t); }, 20); })"
            >
                <template x-for="(s, i) in stats" :key="i">
                    <div class="{{ $variant === 'boxed' ? 'rounded-xl border border-[var(--site-border)] bg-[var(--site-bg)] p-6' : '' }}">
                        <div class="text-4xl font-extrabold text-[var(--tenant-primary)]"><span x-text="s.value.toLocaleString()"></span><span x-text="s.suffix"></span></div>
                        <strong class="mt-2 block text-sm uppercase tracking-wide text-[var(--site-text-muted)]" x-text="s.label"></strong>
                    </div>
                </template>
            </div>
        </div>
    </section>
@endif
