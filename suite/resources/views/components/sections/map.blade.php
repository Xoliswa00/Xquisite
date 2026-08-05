@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'embed';
    $embedUrl = $content['embed_url'] ?? null;
    $address = $content['address'] ?? $tenant->address ?? null;
@endphp

<section class="bg-[var(--site-bg)] py-20">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="text-center">
            @if($content['heading'] ?? null)
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
            @endif
        </div>

        <div class="mt-10 {{ $variant === 'split' ? 'grid gap-8 md:grid-cols-2 md:items-center' : '' }}">
            @if($variant === 'split' && $address)
                <div class="text-left">
                    <h3 class="font-semibold text-[var(--site-text)]">Address</h3>
                    <p class="mt-2 text-[var(--site-text-muted)]">{{ $address }}</p>
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-[var(--site-border)]" style="aspect-ratio: 16/9;">
                @if($embedUrl)
                    <iframe src="{{ $embedUrl }}" class="h-full w-full border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                @else
                    <div class="flex h-full w-full items-center justify-center bg-[var(--site-surface)] text-sm text-[var(--site-text-faint)]">
                        {{ $address ?: 'Add a Google Maps embed URL in the editor to show a map here.' }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
