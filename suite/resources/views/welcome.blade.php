<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Xquisite Creations — Business Management Platform for South African Businesses</title>
    <meta name="description" content="Bookings, POS, online store, property management, and client messaging — one platform, activated module by module. Built for South African businesses, in rands, with local support. Free to start.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:500,600,700,800|inter:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="apple-touch-icon" sizes="57x57" href="/img/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/img/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/img/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/img/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/img/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/img/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
    <link rel="manifest" href="/img/manifest.json">
    <meta name="msapplication-TileColor" content="#002B5B">
    <meta name="msapplication-TileImage" content="/img/ms-icon-144x144.png">
    <meta name="theme-color" content="#002B5B">

    {{-- Open Graph / WhatsApp / Twitter social preview --}}
    <meta property="og:type"          content="website">
    <meta property="og:url"           content="{{ url('/') }}">
    <meta property="og:site_name"     content="Xquisite Creations">
    <meta property="og:title"         content="Business Management Platform for South African Businesses — Xquisite Creations">
    <meta property="og:description"   content="Bookings, POS, online store, property management &amp; client messaging — one platform, activate only what you need. Built in South Africa, works in rands, with PayFast built in. Free to start.">
    <meta property="og:image"         content="{{ url('/img/og-image.jpg') }}">
    <meta property="og:image:width"   content="1200">
    <meta property="og:image:height"  content="630">
    <meta name="twitter:card"         content="summary_large_image">
    <meta name="twitter:title"        content="Business Management Platform for South African Businesses — Xquisite Creations">
    <meta name="twitter:description"  content="Bookings, POS, online store, property management &amp; client messaging — one platform for South African businesses. Activate only what you need.">
    <meta name="twitter:image"        content="{{ url('/img/og-image.jpg') }}">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; }
        .f-mont { font-family: 'Montserrat', sans-serif; }

        /* ─── Xquisite Motion Layer ─────────────────────────────────── */
        @keyframes xqFadeUp  { from{opacity:0;transform:translateY(26px)}to{opacity:1;transform:translateY(0)} }
        @keyframes xqLine    { from{width:0;opacity:0}to{width:3.5rem;opacity:1} }
        @keyframes xqFloatA  { 0%,100%{transform:translate(0,0)}50%{transform:translate(32px,-20px)} }
        @keyframes xqFloatB  { 0%,100%{transform:translate(0,0)}50%{transform:translate(-24px,28px)} }
        @keyframes xqShimmer { 0%{transform:translateX(-130%)}100%{transform:translateX(130%)} }

        /* Hero entrance */
        .xq-enter { opacity:0; animation:xqFadeUp .75s cubic-bezier(.22,1,.36,1) var(--xq-delay,.1s) forwards; }
        .xq-line  { width:0!important; opacity:0; animation:xqLine .8s cubic-bezier(.22,1,.36,1) .05s forwards; }

        /* Ambient orbs */
        .xq-orb      { position:absolute;border-radius:9999px;pointer-events:none;filter:blur(100px);will-change:transform; }
        .xq-orb-blue { width:600px;height:600px;background:rgba(0,120,212,.09);top:-100px;right:-140px;animation:xqFloatA 12s ease-in-out infinite; }
        .xq-orb-gold { width:360px;height:360px;background:rgba(212,175,55,.07);bottom:-80px;left:25%;animation:xqFloatB 16s ease-in-out infinite; }

        /* Grid texture on dark hero */
        .xq-grid {
            background-image: linear-gradient(rgba(0,120,212,.038) 1px,transparent 1px),
                              linear-gradient(90deg,rgba(0,120,212,.038) 1px,transparent 1px);
            background-size: 52px 52px;
        }

        /* Shimmer sweep on primary CTA */
        .xq-shimmer { position:relative;overflow:hidden; }
        .xq-shimmer::after {
            content:'';position:absolute;inset:0;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.22),transparent);
            transform:translateX(-130%);
            animation:xqShimmer 3s ease-in-out 1.8s infinite;
        }

        /* Scroll reveal — scale + fade up */
        .xq-sr { opacity:0;transform:translateY(22px) scale(0.97);transition:opacity .55s cubic-bezier(.22,1,.36,1),transform .55s cubic-bezier(.22,1,.36,1); }
        .xq-sr.xq-in { opacity:1;transform:translateY(0) scale(1); }
        .xq-d1 { transition-delay:.07s; }
        .xq-d2 { transition-delay:.15s; }
        .xq-d3 { transition-delay:.23s; }
        .xq-d4 { transition-delay:.31s; }

        /* ─── Card system — blue→gold sweep + lift ──────────────────────── */
        .xq-card {
            position:relative;overflow:hidden;
            transition:transform 240ms cubic-bezier(.23,1,.32,1),
                        box-shadow 240ms cubic-bezier(.23,1,.32,1),
                        border-color 200ms ease;
        }
        /* Bottom gradient sweep: blue → gold */
        .xq-card::after {
            content:'';position:absolute;bottom:0;left:0;right:0;height:2px;
            background:linear-gradient(90deg,#0078D4 0%,#D4AF37 100%);
            transform:scaleX(0);transform-origin:left center;
            transition:transform 340ms cubic-bezier(.23,1,.32,1);
        }
        .xq-card:hover { transform:translateY(-4px);box-shadow:0 14px 36px rgba(0,43,91,.11); }
        .xq-card:hover::after { transform:scaleX(1); }
        .xq-card:active { transform:translateY(-2px) scale(.99);transition-duration:100ms; }

        /* Icon box: gold ring glow on parent card hover */
        .xq-icon { transition:background-color 200ms ease,box-shadow 220ms ease; }
        .xq-card:hover .xq-icon { box-shadow:0 0 0 3px rgba(212,175,55,.28); }

        /* Dark-bg card — blue edge glow replaces the navy shadow */
        .xq-card.xq-card-dark:hover { box-shadow:0 0 0 1px rgba(0,120,212,.28),0 16px 40px rgba(0,0,0,.32); }

        /* ─── Card child animations (from Emil design pattern) ────────────── */
        @keyframes xqIconIn  { from{transform:scale(0.65);opacity:0} to{transform:scale(1);opacity:1} }
        @keyframes xqTitleIn { from{opacity:0;transform:translateY(7px)} to{opacity:1;transform:none} }
        /* Icon pops in first, heading rises 120ms later */
        .xq-sr .xq-icon      { animation:xqIconIn  .38s cubic-bezier(.23,1,.32,1) .2s  both; animation-play-state:paused; }
        .xq-sr.xq-in .xq-icon { animation-play-state:running; }
        .xq-sr .xq-card-title  { animation:xqTitleIn .42s cubic-bezier(.22,1,.36,1) .32s both; animation-play-state:paused; }
        .xq-sr.xq-in .xq-card-title { animation-play-state:running; }
    </style>
</head>
<body class="antialiased text-[#2D3748] bg-white">

@php
    $all    = \App\Models\PlatformModule::visible()->ordered()->get();
    $active = $all->where('status', 'active');
    $beta   = $all->where('status', 'beta');

    $icons = [
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
        'pos'      => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
        'store'    => 'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72',
        'building' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z',
        'chart'    => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        'widget'   => 'M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253',
        'domain'   => 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
        'star'     => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z',
        'users'    => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
        'map'      => 'M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z',
    ];

    $catColors = [
        'onboarding' => ['label' => 'Getting Started', 'tw' => 'text-[#0078D4]'],
        'training'   => ['label' => 'Training',        'tw' => 'text-[#002B5B]'],
        'support'    => ['label' => 'Support',         'tw' => 'text-[#0078D4]'],
        'custom'     => ['label' => 'Custom Work',     'tw' => 'text-[#D4AF37]'],
    ];
@endphp

{{-- ─── Flash messages ────────────────────────────────────────────────────── --}}
@if(session('error'))
<div class="fixed top-4 left-1/2 -translate-x-1/2 z-[100] px-5 py-3 bg-red-600 text-white text-sm font-medium rounded-xl shadow-lg max-w-sm w-full text-center"
     x-data x-init="setTimeout(() => $el.remove(), 5000)">
    {{ session('error') }}
</div>
@endif

{{-- ─── NAV ──────────────────────────────────────────────────────────────── --}}
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20 gap-4">

            {{-- Logo --}}
            <a href="/" class="flex items-center gap-2.5 shrink-0">
                <img src="/img/android-icon-192x192.png" alt="Xquisite" class="h-9 lg:h-10 w-auto object-contain rounded-lg shrink-0">
                <div class="leading-none">
                    <p class="f-mont font-bold text-sm tracking-wide text-[#002B5B]">XQUISITE</p>
                    <p class="f-mont font-semibold text-[10px] tracking-widest text-[#D4AF37]">CREATIONS</p>
                </div>
            </a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-[#2D3748]">
                <a href="#services"              class="hover:text-[#0078D4] transition-colors">Services</a>
                <a href="#modules"               class="hover:text-[#0078D4] transition-colors">Platform</a>
                <a href="{{ route('about') }}"   class="hover:text-[#0078D4] transition-colors">About</a>
                <a href="{{ route('demo') }}" class="hover:text-[#0078D4] transition-colors">Live Demo</a>
            </nav>

            {{-- Auth --}}
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <a href="{{ route('login') }}" class="text-sm text-[#2D3748] hover:text-[#002B5B] transition px-2 py-1.5">Log in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-[#0078D4] hover:bg-[#0065B8] rounded-lg shadow-sm whitespace-nowrap transition-colors">
                        Get Started
                    </a>
                @endif
            </div>
        </div>
    </div>
</header>

{{-- ─── HERO ─────────────────────────────────────────────────────────────── --}}
<section class="bg-[#002B5B] text-white relative overflow-hidden">
    {{-- Subtle circuit-grid overlay --}}
    <div class="absolute inset-0 xq-grid opacity-70 pointer-events-none"></div>
    {{-- Floating ambient orbs --}}
    <div class="xq-orb xq-orb-blue"></div>
    <div class="xq-orb xq-orb-gold"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 sm:py-28 lg:py-36 relative z-10">
        <div class="max-w-3xl">
            <div class="w-14 h-1 rounded-full bg-[#D4AF37] mb-8 xq-line"></div>

            <h1 class="f-mont text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight mb-5 xq-enter" style="--xq-delay:.15s">
                Your Whole Business.<br>
                <span class="text-[#0078D4]">One Platform.</span>
            </h1>

            <p class="text-base sm:text-lg leading-relaxed mb-4 max-w-2xl text-[#B8D4F0] xq-enter" style="--xq-delay:.28s">
                Bookings, point of sale, online store, property management, and client messaging —
                activate only what your business needs. Built for South African businesses, in rands, with local support.
            </p>

            <p class="f-mont font-semibold text-lg italic mb-10 text-[#D4AF37] xq-enter" style="--xq-delay:.4s">
                Understand Your Why.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 xq-enter" style="--xq-delay:.52s">
                <a href="{{ route('register') }}" class="xq-shimmer inline-flex items-center justify-center gap-2 px-8 py-4 text-white font-semibold bg-[#0078D4] hover:bg-[#0065B8] rounded-xl shadow-lg shadow-[#0078D4]/20 transition-colors">
                    Start Free
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="{{ route('demo') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 font-medium rounded-xl border border-white/20 hover:border-white/40 text-white/70 hover:text-white transition-all duration-200">
                    <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Try Live Demo
                </a>
            </div>

            @php $heroWa = \App\Models\BillingSetting::get('whatsapp_number') ?? config('contact.whatsapp_number'); @endphp
            <p class="mt-4 text-xs text-white/30 xq-enter" style="--xq-delay:.64s">
                Free to start &middot; No card needed &middot; Demo resets every 6 hours
                @if($heroWa)
                &middot; <a href="https://wa.me/{{ $heroWa }}" target="_blank" rel="noopener" class="text-[#25D366] hover:text-[#20b858] transition-colors">Questions? Chat on WhatsApp</a>
                @endif
            </p>
        </div>
    </div>
</section>

{{-- ─── VALUE PILLARS ────────────────────────────────────────────────────── --}}
<section class="bg-[#F5F7FA] py-14 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 lg:gap-10">
            @foreach([
                ['svg' => 'M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5', 'title' => 'Activate What You Need', 'body' => 'Bookings, POS, online store, or property management — turn on only the modules your business actually uses.'],
                ['svg' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z', 'title' => 'Built for South Africa', 'body' => 'Works in rands. PayFast built in. Support that actually responds. No workarounds for a foreign system.'],
                ['svg' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z', 'title' => 'Everything in One Dashboard', 'body' => 'Bookings, sales, stock, tenants, and client messages — all visible in one place. No switching between apps.'],
            ] as $p)
            <div class="xq-card bg-white rounded-2xl p-5 border border-gray-100 flex items-start gap-4 xq-sr xq-d{{ $loop->iteration }}">
                <div class="xq-icon w-10 h-10 bg-[#002B5B] rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $p['svg'] }}"/>
                    </svg>
                </div>
                <div>
                    <h3 class="xq-card-title f-mont font-semibold mb-1 text-sm sm:text-base text-[#002B5B]">{{ $p['title'] }}</h3>
                    <p class="text-sm text-[#2D3748]/70 leading-relaxed">{{ $p['body'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ─── HOW IT WORKS ─────────────────────────────────────────────────────── --}}
<section class="bg-white py-16 sm:py-24">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12 xq-sr">
            <div class="w-12 h-0.5 mx-auto mb-5 rounded-full bg-[#D4AF37]"></div>
            <h2 class="f-mont text-3xl sm:text-4xl font-bold mb-4 text-[#002B5B]">Up and running in minutes</h2>
            <p class="text-sm sm:text-base text-[#2D3748]/70 max-w-xl mx-auto">
                No setup fees. No technical knowledge needed. Three steps and your business is online.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-10 sm:gap-6">
            @foreach([
                ['n' => '1', 'title' => 'Create your account', 'body' => 'Sign up in under 2 minutes. No credit card required. Your account is ready immediately.', 'icon' => 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0ZM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7Z'],
                ['n' => '2', 'title' => 'Activate your modules', 'body' => 'Turn on Bookings, POS, Online Store, Property Management — only what your business needs right now.', 'icon' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
                ['n' => '3', 'title' => 'Run your business', 'body' => 'One login. One dashboard. Bookings, sales, tenants, stock, and client messages — all connected.', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
            ] as $step)
            <div class="flex flex-col items-center text-center xq-sr xq-d{{ $loop->iteration }}">
                <div class="relative mb-6">
                    <div class="w-16 h-16 rounded-full bg-[#002B5B] flex items-center justify-center">
                        <svg class="w-7 h-7 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                        </svg>
                    </div>
                    <div class="absolute -top-1 -right-1 w-6 h-6 bg-[#0078D4] rounded-full flex items-center justify-center">
                        <span class="f-mont font-bold text-white text-xs">{{ $step['n'] }}</span>
                    </div>
                </div>
                <h3 class="f-mont font-bold text-base sm:text-lg mb-2 text-[#002B5B]">{{ $step['title'] }}</h3>
                <p class="text-sm text-[#2D3748]/70 leading-relaxed">{{ $step['body'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="mt-12 text-center">
            <a href="{{ route('register') }}"
               class="xq-shimmer inline-flex items-center gap-2 px-8 py-4 text-white font-semibold bg-[#0078D4] hover:bg-[#0065B8] rounded-xl shadow-lg transition-colors">
                Get started — it's free
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <p class="mt-3 text-xs text-[#2D3748]/40">
                Already have an account? <a href="{{ route('login') }}" class="text-[#0078D4] hover:underline">Log in</a>
            </p>
        </div>

    </div>
</section>

{{-- ─── SERVICES ─────────────────────────────────────────────────────────── --}}
<section class="py-16 sm:py-24 bg-[#F5F7FA]" id="services">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12 lg:mb-16 xq-sr">
            <div class="w-12 h-0.5 mx-auto mb-5 rounded-full bg-[#D4AF37]"></div>
            <h2 class="f-mont text-3xl sm:text-4xl font-bold mb-4 text-[#002B5B]">What We Do</h2>
            <p class="text-sm sm:text-base text-[#2D3748]/70 max-w-2xl mx-auto leading-relaxed">
                Four core practice areas — each built to help your business operate better, grow faster, and decide smarter.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 lg:gap-7">
            @foreach([
                ['icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                 'title' => 'Business Technology',
                 'body'  => 'Custom software, ERP, POS systems, and customer portals built for your specific workflow.',
                 'items' => ['Custom Software Development', 'ERP & POS Systems', 'Customer Portals']],
                ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                 'title' => 'Automation',
                 'body'  => 'Eliminate repetitive work so your team can focus on what matters most.',
                 'items' => ['Workflow Automation', 'Excel & Report Automation', 'Process Optimisation']],
                ['icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                 'title' => 'Data & Intelligence',
                 'body'  => 'Turn your data into decisions through Power BI dashboards, KPI reporting, and analytics.',
                 'items' => ['Power BI Dashboards', 'KPI Reporting', 'Business Analytics']],
                ['icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9',
                 'title' => 'Digital Presence',
                 'body'  => 'Professional websites, e-commerce stores, and online booking systems that convert.',
                 'items' => ['Business Websites', 'E-Commerce Platforms', 'Online Booking Systems']],
            ] as $svc)
            <div class="xq-card group bg-[#F5F7FA] rounded-2xl p-6 lg:p-8 border border-gray-100 hover:bg-white xq-sr xq-d{{ $loop->iteration }}">
                <div class="xq-icon w-12 h-12 bg-[#002B5B] group-hover:bg-[#0078D4] rounded-xl flex items-center justify-center mb-5 shrink-0 transition-colors">
                    <svg class="w-6 h-6 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $svc['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="xq-card-title f-mont font-bold text-base lg:text-lg mb-2 text-[#002B5B]">{{ $svc['title'] }}</h3>
                <p class="text-sm text-[#2D3748]/70 leading-relaxed mb-4">{{ $svc['body'] }}</p>
                <ul class="space-y-1.5">
                    @foreach($svc['items'] as $item)
                    <li class="flex items-center gap-2 text-xs text-[#2D3748]/70">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#D4AF37] shrink-0"></span>
                        {{ $item }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </div>

        {{-- Add-ons --}}
        @php $publicServices = \App\Models\PlatformService::active()->requestable()->ordered()->get()->groupBy('category'); @endphp
        @if ($publicServices->isNotEmpty())
        <div class="mt-16 sm:mt-20">
            <h3 class="f-mont font-bold text-xl sm:text-2xl mb-8 text-center text-[#002B5B]">Professional Add-ons</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-5">
                @foreach ($publicServices->flatten() as $service)
                @php $cat = $catColors[$service->category] ?? ['label' => ucfirst($service->category), 'tw' => 'text-[#2D3748]']; @endphp
                <div class="xq-card bg-white rounded-xl border border-gray-100 p-5 flex flex-col">
                    <p class="text-[10px] sm:text-xs font-semibold uppercase tracking-widest mb-2 {{ $cat['tw'] }}">{{ $cat['label'] }}</p>
                    <h4 class="xq-card-title f-mont font-semibold text-sm sm:text-base mb-1 text-[#002B5B]">{{ $service->name }}</h4>
                    <p class="text-xs sm:text-sm text-[#2D3748]/70 leading-relaxed flex-1">{{ $service->description }}</p>
                    <p class="mt-3 font-bold text-sm sm:text-base text-[#002B5B]">{{ $service->displayPrice() }}</p>
                </div>
                @endforeach
            </div>
            <p class="text-center text-sm mt-8 text-[#2D3748]/50">
                All services are requested through your account dashboard after sign-up.
                <a href="{{ route('register') }}" class="text-[#0078D4] underline hover:no-underline">Get started &rarr;</a>
            </p>
        </div>
        @endif
    </div>
</section>

{{-- ─── PLATFORM MODULES ─────────────────────────────────────────────────── --}}
<section class="bg-[#002B5B] py-16 sm:py-24" id="modules">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-12 lg:mb-16 xq-sr">
            <div class="w-12 h-0.5 mx-auto mb-5 rounded-full bg-[#D4AF37]"></div>
            <h2 class="f-mont text-3xl sm:text-4xl font-bold mb-4 text-white">Platform Modules</h2>
            <p class="text-sm sm:text-base text-[#B8D4F0] max-w-xl mx-auto">
                Everything you need to run your business — activate only what fits, expand as you grow.
            </p>
        </div>

        @if ($active->isNotEmpty())
        <div class="mb-12">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-6">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/25 text-emerald-300 text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Live Now
                </span>
                <span class="text-xs sm:text-sm text-[#B8D4F0]/60">Available — activate from your settings</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                @foreach ($active as $module)
                <div class="xq-card xq-card-dark group bg-white/[0.07] rounded-xl border border-white/[0.08] p-5 lg:p-6 flex flex-col xq-sr xq-d{{ min($loop->iteration, 4) }}">
                    <div class="xq-icon w-10 h-10 bg-[#0078D4] group-hover:bg-[#D4AF37] rounded-lg flex items-center justify-center mb-4 shrink-0 transition-colors">
                        <svg class="w-5 h-5 text-white group-hover:text-[#002B5B] transition-colors" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$module['icon']] ?? $icons['chart'] }}"/>
                        </svg>
                    </div>
                    <h3 class="xq-card-title f-mont font-semibold text-sm lg:text-base mb-1 text-white">{{ $module['name'] }}</h3>
                    <p class="text-xs sm:text-sm text-[#B8D4F0]/70 leading-relaxed flex-1">{{ $module['description'] }}</p>
                    <p class="mt-3 text-xs text-[#B8D4F0]/40">From <span class="font-semibold text-[#D4AF37]">R{{ number_format($module['price'], 0) }}/mo</span></p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if ($beta->isNotEmpty())
        <div class="mb-12">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-6">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#D4AF37]/15 border border-[#D4AF37]/25 text-[#D4AF37] text-xs font-semibold">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#D4AF37]"></span>In Testing
                </span>
                <span class="text-xs sm:text-sm text-[#B8D4F0]/60">Launching to all clients soon</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 lg:gap-6">
                @foreach ($beta as $module)
                <div class="xq-card xq-card-dark bg-white/[0.04] rounded-xl border border-white/[0.06] p-5 lg:p-6 flex flex-col xq-sr xq-d{{ min($loop->iteration, 4) }}">
                    <div class="xq-icon w-10 h-10 bg-[#0078D4]/40 rounded-lg flex items-center justify-center mb-4 shrink-0">
                        <svg class="w-5 h-5 text-[#D4AF37]/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$module['icon']] ?? $icons['chart'] }}"/>
                        </svg>
                    </div>
                    <div class="flex items-start justify-between mb-1">
                        <h3 class="xq-card-title f-mont font-semibold text-sm lg:text-base text-white/55">{{ $module['name'] }}</h3>
                        <span class="ml-1.5 shrink-0 text-[10px] px-1.5 py-0.5 rounded bg-[#D4AF37]/15 text-[#D4AF37]/70 border border-[#D4AF37]/20 font-medium">Beta</span>
                    </div>
                    <p class="text-xs sm:text-sm text-[#B8D4F0]/45 leading-relaxed flex-1">{{ $module['description'] }}</p>
                    <p class="mt-3 text-xs text-white/25">From <span class="font-semibold text-[#D4AF37]/55">R{{ number_format($module['price'], 0) }}/mo</span></p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>

{{-- ─── COMING SOON ────────────────────────────────────────────────────── --}}
@php $comingSoon = \App\Models\PlatformModule::comingSoon()->visible()->ordered()->get(); @endphp
@if($comingSoon->isNotEmpty())
<section class="bg-[#001A3A] py-16 sm:py-24 relative overflow-hidden">
    <div class="absolute inset-0 xq-grid opacity-30 pointer-events-none"></div>
    <div class="xq-orb xq-orb-gold" style="opacity:.35"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

        <div class="text-center mb-12 xq-sr">
            <div class="w-12 h-0.5 mx-auto mb-5 rounded-full bg-[#D4AF37]"></div>
            <span class="inline-block px-3 py-1 rounded-full text-[10px] f-mont font-bold uppercase tracking-widest bg-[#D4AF37]/15 text-[#D4AF37] border border-[#D4AF37]/20 mb-5">
                What's Next
            </span>
            <h2 class="f-mont text-3xl sm:text-4xl font-bold text-white mb-4">Coming Soon to the Platform</h2>
            <p class="text-sm sm:text-base text-[#B8D4F0]/60 max-w-xl mx-auto leading-relaxed">
                We build what your business actually needs — here's what's already in the pipeline.
            </p>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($comingSoon as $module)
            <div class="xq-sr xq-d{{ ($loop->index % 4) + 1 }} group bg-white/5 border border-white/10 rounded-2xl p-6 flex flex-col gap-4
                        hover:border-[#D4AF37]/30 hover:bg-white/[.07] transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div class="w-10 h-10 rounded-xl bg-[#D4AF37]/10 border border-[#D4AF37]/20 flex items-center justify-center
                                group-hover:bg-[#D4AF37]/20 transition-colors">
                        <svg class="w-5 h-5 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$module->icon] ?? $icons['chart'] }}"/>
                        </svg>
                    </div>
                    <span class="text-[10px] font-bold f-mont uppercase tracking-widest text-[#D4AF37]
                                 bg-[#D4AF37]/10 border border-[#D4AF37]/20 px-2.5 py-1 rounded-full">
                        Soon
                    </span>
                </div>
                <div class="flex-1">
                    <p class="font-bold text-white f-mont text-sm mb-2 leading-snug">{{ $module->name }}</p>
                    <p class="text-xs text-[#B8D4F0]/50 leading-relaxed">{{ $module->description }}</p>
                </div>
                @if($module->price)
                <p class="text-xs text-white/25">
                    From <span class="font-semibold text-[#D4AF37]/60">R{{ number_format($module->price, 0) }}/mo</span>
                </p>
                @endif
            </div>
            @endforeach
        </div>

        <p class="text-center mt-10 text-xs text-white/25">
            Want early access?
            <a href="{{ route('register') }}" class="text-[#D4AF37]/60 hover:text-[#D4AF37] underline underline-offset-2 transition-colors">
                Sign up now
            </a>
            and we'll notify you when these go live.
        </p>
    </div>
</section>
@endif

{{-- ─── TESTIMONIALS ─────────────────────────────────────────────────────── --}}
@php $testimonials = \App\Models\Review::public()->limit(6)->get(); @endphp
@if ($testimonials->isNotEmpty())
<section class="py-16 sm:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 xq-sr">
            <div class="w-12 h-0.5 mx-auto mb-5 rounded-full bg-[#D4AF37]"></div>
            <h2 class="f-mont text-3xl sm:text-4xl font-bold mb-4 text-[#002B5B]">Businesses Running on Xquisite</h2>
            <p class="text-sm sm:text-base text-[#2D3748]/60">Real feedback from real operators.</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
            @foreach ($testimonials as $review)
            <div class="xq-card bg-[#F5F7FA] rounded-2xl p-5 sm:p-6 flex flex-col xq-sr xq-d{{ ($loop->index % 4) + 1 }} {{ $review->is_featured ? 'ring-2 ring-[#0078D4]/30 border border-[#0078D4]/50' : 'border border-[#E8EBF0]' }}">
                <div class="flex items-center gap-0.5 mb-3">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ $i <= $review->rating ? 'text-[#D4AF37]' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>
                @if ($review->title)
                    <p class="f-mont font-semibold text-xs sm:text-sm mb-2 text-[#002B5B]">{{ $review->title }}</p>
                @endif
                <p class="text-xs sm:text-sm text-[#2D3748]/70 leading-relaxed flex-1 line-clamp-4">{{ $review->body }}</p>
                <div class="mt-4 pt-4 border-t border-gray-200 flex items-center gap-2">
                    <div class="w-7 h-7 bg-[#002B5B] rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">
                        {{ strtoupper(substr($review->display_name ?? 'B', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-[#002B5B] truncate">{{ $review->display_name ?? 'Business Owner' }}</p>
                        @if ($review->business_type)
                            <p class="text-[10px] sm:text-xs text-[#2D3748]/50 truncate">{{ $review->business_type }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ─── PRICING ──────────────────────────────────────────────────────────── --}}
@php $plans = \App\Models\Plan::active()->ordered()->with('planModules.platformModule')->get(); @endphp
@if ($plans->isNotEmpty())
<section class="bg-[#F5F7FA] py-16 sm:py-24" id="pricing">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 lg:mb-16 xq-sr">
            <div class="w-12 h-0.5 mx-auto mb-5 rounded-full bg-[#D4AF37]"></div>
            <h2 class="f-mont text-3xl sm:text-4xl font-bold mb-4 text-[#002B5B]">Simple, Transparent Pricing</h2>
            <p class="text-sm sm:text-base text-[#2D3748]/60">Bundle and save — or activate individual modules from R99/mo.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch">
            @foreach ($plans as $plan)
            @php $f = $plan->is_featured; @endphp
            <div class="xq-card rounded-2xl flex flex-col xq-sr xq-d{{ $loop->iteration }} {{ $f ? 'bg-[#002B5B] shadow-2xl ring-2 ring-[#D4AF37]/50' : 'bg-white shadow-sm border border-gray-200' }}">

                {{-- Gold accent line on top --}}
                <div class="h-1 {{ $f ? 'bg-[#D4AF37]' : 'bg-[#0078D4]/25' }}"></div>

                @if ($f)
                {{-- Featured badge --}}
                <div class="flex items-center justify-center gap-1.5 py-3">
                    <svg class="w-3 h-3 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                    <span class="f-mont text-[10px] font-bold uppercase tracking-[.2em] text-[#D4AF37]">Most Popular</span>
                    <svg class="w-3 h-3 text-[#D4AF37]" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                @endif

                <div class="p-6 sm:p-8 flex flex-col flex-1">
                    {{-- Plan name & tagline --}}
                    <div class="mb-5">
                        <h3 class="f-mont text-xl font-bold {{ $f ? 'text-white' : 'text-[#002B5B]' }}">{{ $plan->name }}</h3>
                        @if ($plan->tagline)
                            <p class="text-sm mt-1.5 leading-relaxed {{ $f ? 'text-[#B8D4F0]' : 'text-[#2D3748]/55' }}">{{ $plan->tagline }}</p>
                        @endif
                    </div>

                    {{-- Price --}}
                    <div class="mb-6">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl sm:text-5xl font-bold {{ $f ? 'text-white' : 'text-[#002B5B]' }}">R{{ number_format($plan->price_monthly, 0) }}</span>
                            <span class="text-sm {{ $f ? 'text-[#B8D4F0]' : 'text-[#2D3748]/45' }}">/month</span>
                        </div>
                        @if ($plan->price_annual)
                            <p class="mt-1.5 text-xs {{ $f ? 'text-emerald-300' : 'text-emerald-600' }}">
                                R{{ number_format($plan->price_annual, 0) }}/mo billed annually &middot; save {{ $plan->annualDiscountPercent() }}%
                            </p>
                        @endif
                    </div>

                    {{-- CTA button --}}
                    <a href="{{ route('register') }}"
                       class="block text-center py-3.5 rounded-xl f-mont font-semibold text-sm transition-colors mb-7
                              {{ $f ? 'bg-[#D4AF37] hover:bg-[#C09B28] text-[#002B5B]' : 'bg-[#002B5B] hover:bg-[#003872] text-white' }}">
                        Get started &rarr;
                    </a>

                    {{-- Features --}}
                    <div class="border-t {{ $f ? 'border-white/10' : 'border-gray-100' }} pt-5 flex-1">
                        <p class="text-[10px] f-mont font-bold uppercase tracking-widest mb-4 {{ $f ? 'text-[#D4AF37]' : 'text-[#0078D4]' }}">
                            What's included
                        </p>
                        <ul class="space-y-3">
                            @foreach ($plan->planModules as $pm)
                            @php $iconKey = $pm->platformModule?->icon ?? 'chart'; @endphp
                            <li class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0
                                            {{ $f ? 'bg-white/10' : 'bg-[#F0F7FF]' }}">
                                    <svg class="w-3.5 h-3.5 {{ $f ? 'text-[#D4AF37]' : 'text-[#0078D4]' }}"
                                         fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$iconKey] ?? $icons['chart'] }}"/>
                                    </svg>
                                </div>
                                <span class="text-sm {{ $f ? 'text-[#E8F2FA]' : 'text-[#2D3748]' }}">
                                    {{ $pm->platformModule?->name ?? $pm->module_key }}
                                </span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <p class="text-center text-xs text-[#2D3748]/40 mt-10">
            All plans include a 14-day free trial &middot; No credit card required &middot; Cancel anytime
        </p>
    </div>
</section>
@endif

{{-- ─── CTA ──────────────────────────────────────────────────────────────── --}}
<section class="bg-[#002B5B] py-16 sm:py-24 relative overflow-hidden">
    <div class="absolute inset-0 xq-grid opacity-50 pointer-events-none"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 xq-sr">
        <div class="w-14 h-0.5 mx-auto mb-8 rounded-full bg-[#D4AF37]"></div>
        <h2 class="f-mont text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-5">
            Ready to run your business<br>from one place?
        </h2>
        <p class="text-base leading-relaxed mb-3 max-w-xl mx-auto text-[#B8D4F0]">
            Sign up free, activate your first module, and be up and running before end of day.
            No credit card. No complexity. Just your business, running better.
        </p>
        <p class="f-mont font-semibold text-lg italic mb-10 text-[#D4AF37]">Understand Your Why.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="px-8 py-4 text-white font-semibold bg-[#0078D4] hover:bg-[#0065B8] rounded-xl shadow-lg transition-colors">
                Get Started Free
            </a>
            <a href="{{ route('login') }}" class="px-8 py-4 font-semibold text-white rounded-xl border border-white/25 hover:border-[#D4AF37] hover:text-[#D4AF37] transition-colors">
                Log In
            </a>
        </div>
        <p class="mt-8 text-xs text-white/30">
            Xquisite Creations (Pty) Ltd &mdash; Built for South African Business.
        </p>
    </div>
</section>

<x-whatsapp-button />
<script>
(function () {
    'use strict';
    var els = document.querySelectorAll('.xq-sr');
    if (!els.length) return;

    function reveal(el) {
        el.classList.add('xq-in');
        // After the transition finishes, clean up so Tailwind hover effects work normally
        var delay = parseFloat(getComputedStyle(el).transitionDelay) * 1000 || 0;
        var dur   = parseFloat(getComputedStyle(el).transitionDuration) * 1000 || 600;
        setTimeout(function () {
            el.classList.remove('xq-sr', 'xq-in', 'xq-d1', 'xq-d2', 'xq-d3', 'xq-d4');
        }, delay + dur + 80);
    }

    if (!('IntersectionObserver' in window)) {
        els.forEach(reveal);
        return;
    }

    var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            reveal(entry.target);
            io.unobserve(entry.target);
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    els.forEach(function (el) { io.observe(el); });
})();
</script>
</body>
</html>
