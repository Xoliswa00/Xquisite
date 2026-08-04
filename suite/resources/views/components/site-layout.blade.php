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
            --tenant-primary: {{ $branding->primary_color ?? '#0078D4' }};
            --tenant-secondary: {{ $branding->secondary_color ?? '#002B5B' }};
            --tenant-accent: {{ $branding->accent_color ?? '#D4AF37' }};
            --tenant-font-heading: {{ $headingFont['stack'] }};
            --tenant-font-body: {{ $bodyFont['stack'] }};
        }
        body { font-family: var(--tenant-font-body); }
        h1, h2, h3, h4, h5, h6 { font-family: var(--tenant-font-heading); }
    </style>
</head>
<body class="antialiased">
    {{ $slot }}
</body>
</html>
