@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'grid-4';
    $images = collect($content['images'] ?? [])->filter(fn ($i) => !empty($i['url']))->values();
    $handle = $content['handle'] ?? null;
    $cols = $variant === 'grid-3' ? 'sm:grid-cols-3' : 'sm:grid-cols-4';
@endphp

@if($images->isNotEmpty() || $handle || $editing)
    <section class="bg-[var(--site-bg)] py-20">
        <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
            @if($content['heading'] ?? null)
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
            @endif
            @if($handle)
                <a href="https://instagram.com/{{ $handle }}" target="_blank" rel="noopener" class="mt-2 inline-block text-sm text-[var(--tenant-primary)] hover:underline">{{ '@' . $handle }}</a>
            @endif

            @if($images->isNotEmpty())
                <div class="mt-8 grid grid-cols-2 gap-2 {{ $cols }}">
                    @foreach($images as $image)
                        <a href="{{ $handle ? 'https://instagram.com/' . $handle : '#' }}" target="_blank" rel="noopener" class="group aspect-square overflow-hidden rounded-lg border border-[var(--site-border)]">
                            <img src="{{ $image['url'] }}" alt="Instagram photo" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
