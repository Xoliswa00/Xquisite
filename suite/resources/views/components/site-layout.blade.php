@props(['tenant', 'branding', 'template'])

@php
    $fonts = config('branding.fonts');
    $headingFont = $fonts[$branding->heading_font ?? 'inter'] ?? $fonts['inter'];
    $bodyFont = $fonts[$branding->body_font ?? 'inter'] ?? $fonts['inter'];
    $googleFamilies = collect([$headingFont['google'], $bodyFont['google']])->unique()->implode('&family=');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        (function () {
            try {
                if (localStorage.getItem('xq-site-theme') === 'dark') {
                    document.documentElement.classList.add('dark');
                }
                /* No matchMedia/OS fallback — first visit always defaults to light. */
            } catch (e) {}
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? $tenant->name }}</title>
    <meta name="description" content="{{ Str::limit($branding->description ?? $tenant->name, 160) }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family={{ $googleFamilies }}&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @if($branding->favicon_url)
        <link rel="shortcut icon" href="{{ $branding->favicon_url }}">
    @else
        <link rel="shortcut icon" href="{{ asset('img/favicon-32x32.png') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --tenant-primary: {{ $branding->primary_color ?? $template->default_primary_color ?? '#0078D4' }};
            --tenant-secondary: {{ $branding->secondary_color ?? $template->default_secondary_color ?? '#002B5B' }};
            --tenant-accent: {{ $branding->accent_color ?? $template->default_accent_color ?? '#D4AF37' }};
            --tenant-font-heading: {{ $headingFont['stack'] }};
            --tenant-font-body: {{ $bodyFont['stack'] }};
        }
        body { font-family: var(--tenant-font-body); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--tenant-font-heading); }
    </style>
</head>
<body class="antialiased">
    {{ $slot }}

    @if($template->key !== 'grandure-coming-soon')
        <button type="button" onclick="xqToggleSiteTheme()" aria-label="Toggle dark mode"
                class="fixed bottom-6 left-6 z-50 w-11 h-11 rounded-full shadow-lg flex items-center justify-center transition-colors"
                style="background: var(--site-surface); border: 1px solid var(--site-border); color: var(--site-text);">
            <svg class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <svg class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        </button>
        <script>
            window.xqToggleSiteTheme = function () {
                const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
                document.documentElement.classList.toggle('dark', next === 'dark');
                localStorage.setItem('xq-site-theme', next);
            };
        </script>
    @endif
</body>
</html>
