@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'framed';
    $videoUrl = $content['video_url'] ?? null;
@endphp

@if($videoUrl || $editing)
    <section class="bg-[var(--site-surface)] py-20">
        <div class="mx-auto {{ $variant === 'full-width' ? 'max-w-6xl' : 'max-w-3xl' }} px-4 text-center sm:px-6">
            @if($content['heading'] ?? null)
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
            @endif

            <div class="mt-8 overflow-hidden {{ $variant === 'framed' ? 'rounded-xl border border-[var(--site-border)]' : '' }}" style="aspect-ratio: 16/9;">
                @if($videoUrl)
                    <iframe src="{{ $videoUrl }}" class="h-full w-full border-0" loading="lazy" allowfullscreen></iframe>
                @else
                    <div class="flex h-full w-full items-center justify-center bg-[var(--site-bg)] text-sm text-[var(--site-text-faint)]">
                        Add a video URL in the editor.
                    </div>
                @endif
            </div>

            @if($content['caption'] ?? null)
                <p class="mt-4 text-sm text-[var(--site-text-faint)]">{{ $content['caption'] }}</p>
            @endif
        </div>
    </section>
@endif
