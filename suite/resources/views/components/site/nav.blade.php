@props(['tenant', 'sections'])

@php
    $links = $sections
        ->where('is_visible', true)
        ->values()
        ->map(fn ($s, $i) => [
            'href' => '#section-' . $s->id,
            'label' => $i === 0 && $s->type === 'hero' ? 'Home' : ($s->content['heading'] ?? $s->label()),
        ])
        ->filter(fn ($l) => filled($l['label']))
        ->values();
@endphp

<header x-data="{ mobileNavOpen: false }" class="sticky top-0 z-40 border-b border-[var(--site-border)] bg-[var(--site-nav-bg)] backdrop-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
        <a href="#" class="flex shrink-0 items-center gap-2">
            @if($tenant->logo_url)
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-9 w-auto object-contain">
            @else
                <span class="font-display text-lg font-bold text-[var(--site-text)]">{{ $tenant->name }}</span>
            @endif
        </a>
        <nav class="hidden gap-7 text-sm font-medium text-[var(--site-text)] md:flex">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}" class="transition hover:text-[var(--tenant-primary)]">{{ $link['label'] }}</a>
            @endforeach
        </nav>
        <button @click="mobileNavOpen = !mobileNavOpen" class="shrink-0 text-[var(--site-text)] md:hidden" aria-label="Toggle navigation">
            <i class="fa" :class="mobileNavOpen ? 'fa-times' : 'fa-bars'"></i>
        </button>
    </div>
    <nav x-cloak x-show="mobileNavOpen" x-transition class="border-t border-[var(--site-border)] bg-[var(--site-bg)] px-4 py-3 md:hidden">
        @foreach ($links as $link)
            <a href="{{ $link['href'] }}" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">{{ $link['label'] }}</a>
        @endforeach
    </nav>
</header>
