@props(['tenant', 'branding', 'sections'])

@php
    $contactSection = $sections->firstWhere('type', 'contact');
    $socials = $contactSection?->content['socials'] ?? $branding->socials ?? [];
    $icons = ['facebook' => 'fa-facebook', 'instagram' => 'fa-instagram', 'twitter' => 'fa-twitter', 'linkedin' => 'fa-linkedin', 'youtube' => 'fa-youtube', 'tiktok' => 'fa-tiktok'];
@endphp

<footer class="border-t border-[var(--site-border)] bg-[var(--site-bg)] py-10">
    <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
        <a href="#" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--site-border)] text-[var(--site-text-faint)] transition hover:border-[var(--tenant-primary)] hover:text-[var(--tenant-primary)]" aria-label="Back to top">
            <i class="fa fa-angle-double-up"></i>
        </a>
        @if (array_filter($socials))
            <ul class="mt-6 flex justify-center gap-4">
                @foreach ($icons as $platform => $icon)
                    @if (!empty($socials[$platform]))
                        <li>
                            <a href="{{ $socials[$platform] }}" target="_blank" rel="noopener"
                               class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--site-border)] text-[var(--site-text-faint)] transition hover:border-[var(--tenant-primary)] hover:text-[var(--tenant-primary)]">
                                <i class="fa {{ $icon }}"></i>
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        @endif
        <p class="mt-6 text-xs text-[var(--site-text-faint)]">&copy; {{ now()->year }} {{ $tenant->name }}. All rights reserved.</p>
    </div>
</footer>
