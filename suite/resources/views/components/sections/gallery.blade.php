@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'grid';
    $images = collect($content['images'] ?? [])->filter(fn ($i) => !empty($i['url']))->values();
    $filters = collect($content['filters'] ?? []);
    $gridClass = $variant === 'masonry' ? 'columns-2 sm:columns-3 lg:columns-4 gap-3 [&>*]:mb-3 [&>*]:break-inside-avoid' : 'grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4';
@endphp

<section
    class="bg-[var(--site-surface)] py-20"
    x-data="{
        filter: '*',
        open: false,
        active: null,
        images: {{ \Illuminate\Support\Js::from($images->all()) }},
    }"
>
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="text-center">
            @if($content['heading'] ?? null)
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
            @endif
            @if($content['subtext'] ?? null)
                <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
            @endif
        </div>

        @if($filters->isNotEmpty())
            <div class="mt-8 flex flex-wrap justify-center gap-2">
                <button @click="filter = '*'" class="rounded-full border px-4 py-1.5 text-sm transition" :class="filter === '*' ? 'border-[var(--tenant-primary)] bg-[var(--tenant-primary)]/10 text-[var(--tenant-primary)]' : 'border-[var(--site-border-2)] text-[var(--site-text-muted)] hover:border-gray-400'">All</button>
                @foreach($filters as $f)
                    <button @click="filter = '{{ $f['key'] }}'" class="rounded-full border px-4 py-1.5 text-sm transition" :class="filter === '{{ $f['key'] }}' ? 'border-[var(--tenant-primary)] bg-[var(--tenant-primary)]/10 text-[var(--tenant-primary)]' : 'border-[var(--site-border-2)] text-[var(--site-text-muted)] hover:border-gray-400'">{{ $f['label'] }}</button>
                @endforeach
            </div>
        @endif

        <div class="mt-10 {{ $gridClass }}">
            <template x-for="(item, i) in images" :key="i">
                <button
                    x-show="filter === '*' || (item.tags || []).includes(filter)"
                    @click="active = item.url; open = true"
                    class="group relative aspect-square w-full overflow-hidden rounded-lg border border-[var(--site-border)]"
                >
                    <img :src="item.url" alt="Gallery photo" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                    <span class="absolute inset-0 flex items-center justify-center bg-black/0 text-white opacity-0 transition group-hover:bg-black/50 group-hover:opacity-100">
                        <i class="fa fa-search-plus"></i>
                    </span>
                </button>
            </template>
        </div>
    </div>

    <div
        x-cloak x-show="open" x-transition.opacity
        @keydown.escape.window="open = false"
        @click.self="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
    >
        <button @click="open = false" class="absolute right-6 top-6 text-2xl text-white/80 hover:text-white" aria-label="Close">
            <i class="fa fa-times"></i>
        </button>
        <img :src="active" alt="Gallery photo enlarged" class="max-h-[85vh] max-w-full rounded-lg object-contain">
    </div>
</section>
