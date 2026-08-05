@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'image-left';
    $imageFirst = $variant !== 'image-right';
    $bullets = collect($content['bullets'] ?? [])->filter(fn ($b) => !empty($b['text']))->values();
@endphp

<section class="bg-[var(--site-surface)] py-20">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 md:grid-cols-2 md:items-center">
        @if($imageFirst && ($content['image_url'] ?? null))
            <img src="{{ $content['image_url'] }}" alt="{{ $tenant->name }}" class="w-full rounded-xl object-cover">
        @endif

        <div class="text-left">
            <h2 class="font-display text-2xl font-bold text-[var(--site-text)] sm:text-3xl">{{ $content['heading'] ?? 'Who We Are' }}</h2>
            <p class="mt-4 text-[var(--site-text-muted)]">{{ $content['body'] ?? 'Tell your customers about your business — add a description in the editor.' }}</p>

            @if($bullets->isNotEmpty())
                <ul class="mt-6 space-y-2">
                    @foreach($bullets as $bullet)
                        <li class="flex items-center gap-2 text-[var(--site-text)]">
                            <i class="fa fa-angle-double-right text-[var(--tenant-accent)]"></i>
                            <span>{{ $bullet['text'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        @if(!$imageFirst && ($content['image_url'] ?? null))
            <img src="{{ $content['image_url'] }}" alt="{{ $tenant->name }}" class="w-full rounded-xl object-cover">
        @endif
    </div>
</section>
