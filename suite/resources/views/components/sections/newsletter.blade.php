@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'centered-card';
@endphp

@if($variant === 'inline-bar')
    <section class="bg-[var(--tenant-primary)] py-8">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 text-center sm:flex-row sm:text-left sm:px-6">
            <div>
                <h3 class="text-lg font-semibold text-white">{{ $content['heading'] ?? 'Stay in the Loop' }}</h3>
                @if($content['subtext'] ?? null)
                    <p class="text-sm text-white/80">{{ $content['subtext'] }}</p>
                @endif
            </div>
            <x-sections.newsletter-form :tenant="$tenant" class="w-full sm:w-auto" />
        </div>
    </section>
@else
    <section class="bg-[var(--site-surface)] py-20">
        <div class="mx-auto max-w-xl px-4 text-center sm:px-6">
            <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] ?? 'Stay in the Loop' }}</h2>
            @if($content['subtext'] ?? null)
                <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
            @endif
            <div class="mt-8">
                <x-sections.newsletter-form :tenant="$tenant" />
            </div>
        </div>
    </section>
@endif
