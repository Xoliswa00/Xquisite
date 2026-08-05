@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'carousel';
    $headline = $content['headline'] ?: $tenant->name;
    $slides = collect($content['slides'] ?? [])->filter(fn ($s) => !empty($s['image_url']))->values();
    if ($slides->isEmpty()) {
        $slides = collect([[
            'image_url' => $content['background_image_url'] ?? null,
            'headline' => $headline,
            'subheadline' => $content['subheadline'] ?? '',
        ]]);
    }
@endphp

@if($variant === 'carousel' && $slides->count() > 1)
    <section
        class="relative flex h-[80vh] min-h-[520px] items-center justify-center overflow-hidden"
        x-data="{ slide: 0, slides: {{ \Illuminate\Support\Js::from($slides->all()) }} }"
        x-init="setInterval(() => slide = (slide + 1) % slides.length, 5000)"
    >
        <template x-for="(s, i) in slides" :key="i">
            <div
                x-show="slide === i"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="absolute inset-0 bg-cover bg-center"
                :style="`background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), url(${s.image_url})`"
            >
                <div class="flex h-full flex-col items-center justify-center px-4 text-center">
                    @if($content['eyebrow'] ?? null)
                        <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--tenant-accent)]">{{ $content['eyebrow'] }}</p>
                    @endif
                    <h1 class="font-display text-4xl font-bold text-white sm:text-5xl" x-text="s.headline"></h1>
                    <p class="mt-4 max-w-md text-white/80" x-text="s.subheadline"></p>
                    @if($content['cta_text'] ?? null)
                        <a href="{{ $content['cta_link'] ?: '#' }}" class="mt-8 rounded-lg bg-[var(--tenant-primary)] px-8 py-3 text-sm font-semibold text-white transition hover:brightness-90">{{ $content['cta_text'] }}</a>
                    @endif
                </div>
            </div>
        </template>

        <div class="absolute bottom-6 left-1/2 flex -translate-x-1/2 gap-2">
            <template x-for="(s, i) in slides" :key="i">
                <button @click="slide = i" class="h-2.5 w-2.5 rounded-full transition" :class="slide === i ? 'bg-[var(--tenant-accent)]' : 'bg-white/40'"></button>
            </template>
        </div>
    </section>
@else
    <section
        class="relative flex min-h-[80vh] items-center justify-center overflow-hidden bg-cover bg-center text-center"
        @if($slides->first()['image_url'] ?? null)
            style="background-image: linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.65)), url('{{ $slides->first()['image_url'] }}')"
        @endif
    >
        <div class="px-4">
            @if($content['eyebrow'] ?? null)
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--tenant-accent)]">{{ $content['eyebrow'] }}</p>
            @endif
            <h1 class="mt-3 font-display text-4xl font-extrabold text-white sm:text-5xl">{{ $headline }}</h1>
            @if($content['subheadline'] ?? null)
                <p class="mx-auto mt-4 max-w-lg text-white/80">{{ $content['subheadline'] }}</p>
            @endif
            @if($content['cta_text'] ?? null)
                <a href="{{ $content['cta_link'] ?: '#' }}" class="mt-8 inline-block rounded-lg bg-[var(--tenant-primary)] px-8 py-3 text-sm font-semibold text-white transition hover:brightness-90">{{ $content['cta_text'] }}</a>
            @endif
        </div>
    </section>
@endif
