<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Live Demo — Xquisite Creations</title>
    <meta name="description" content="Explore the full Xquisite platform live — bookings, clients, POS, analytics and more. No sign-up needed.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:500,600,700,800|inter:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <meta name="theme-color" content="#0f172a">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; }
        .f-mont { font-family: 'Montserrat', sans-serif; }

        /* ── Entrance animations ── */
        @keyframes fadeUp   { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }
        @keyframes shimmer  { 0%{transform:translateX(-130%)} 100%{transform:translateX(130%)} }
        @keyframes floatA   { 0%,100%{transform:translate(0,0)} 50%{transform:translate(28px,-18px)} }
        @keyframes floatB   { 0%,100%{transform:translate(0,0)} 50%{transform:translate(-22px,24px)} }
        @keyframes pulse-dot{ 0%,100%{opacity:.5;transform:scale(1)} 50%{opacity:1;transform:scale(1.3)} }

        .xq-enter { opacity:0; animation:fadeUp .7s cubic-bezier(.22,1,.36,1) var(--d,.1s) forwards; }

        /* ── Shimmer CTA ── */
        .btn-shimmer { position:relative; overflow:hidden; }
        .btn-shimmer::after {
            content:''; position:absolute; inset:0;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);
            transform:translateX(-130%);
            animation:shimmer 3s ease-in-out 2s infinite;
        }

        /* ── Ambient orbs ── */
        .orb { position:absolute;border-radius:9999px;pointer-events:none;filter:blur(120px); }
        .orb-blue { width:700px;height:700px;background:rgba(0,120,212,.12);top:-200px;right:-200px;animation:floatA 14s ease-in-out infinite; }
        .orb-gold { width:400px;height:400px;background:rgba(212,175,55,.07);bottom:-100px;left:20%;animation:floatB 18s ease-in-out infinite; }

        /* ── Scroll reveal ── */
        .sr { opacity:0;transform:translateY(20px);transition:opacity .5s cubic-bezier(.22,1,.36,1),transform .5s cubic-bezier(.22,1,.36,1); }
        .sr.in { opacity:1;transform:none; }
        .d1{transition-delay:.05s}.d2{transition-delay:.12s}.d3{transition-delay:.19s}.d4{transition-delay:.26s}.d5{transition-delay:.33s}.d6{transition-delay:.4s}

        /* ── Cards ── */
        .ct-card {
            background:#1e293b;
            border:1px solid rgba(148,163,184,.12);
            border-radius:16px;
            transition:border-color .2s, box-shadow .2s, transform .2s;
        }
        .ct-card:hover {
            border-color:rgba(0,120,212,.35);
            box-shadow:0 0 0 1px rgba(0,120,212,.15), 0 16px 48px rgba(0,0,0,.4);
            transform:translateY(-3px);
        }

        /* ── Icon box ── */
        .ct-icon { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }

        /* ── Dashboard mockup ── */
        .mock-chrome { background:#0f172a;border-radius:14px;overflow:hidden;box-shadow:0 40px 100px rgba(0,0,0,.6),0 0 0 1px rgba(255,255,255,.06); }
        .mock-bar    { height:34px;background:#1e293b;display:flex;align-items:center;padding:0 12px;gap:7px;border-bottom:1px solid rgba(255,255,255,.05); }
        .mock-dot    { width:9px;height:9px;border-radius:50%; }
        .mock-addr   { flex:1;margin:0 10px;height:18px;background:#0f172a;border-radius:4px;display:flex;align-items:center;padding:0 8px; }
        .mock-sidebar{ width:180px;background:#1e293b;border-right:1px solid rgba(255,255,255,.05);flex-shrink:0;padding:10px 0; }
        .mock-nav    { display:flex;align-items:center;gap:7px;padding:6px 12px;font-size:11px;color:rgba(148,163,184,.8); }
        .mock-nav.on { background:linear-gradient(90deg,rgba(0,120,212,.22),transparent);color:white;border-left:2px solid #0078D4;padding-left:10px; }
        .mock-nav svg{ width:13px;height:13px;flex-shrink:0; }
        .mock-stat   { background:#1e293b;border-radius:8px;padding:12px;border:1px solid rgba(255,255,255,.06); }
        .mock-row    { background:#1e293b;border-radius:6px;margin-bottom:5px;padding:9px 11px;display:flex;align-items:center;gap:9px;border:1px solid rgba(255,255,255,.04); }
        .mock-badge  { font-size:9px;padding:2px 7px;border-radius:20px;font-weight:600;white-space:nowrap; }

        /* ── Stat card ── */
        .stat-card { background:#1e293b;border-radius:12px;border:1px solid rgba(148,163,184,.1);padding:20px 24px; }
        .stat-value{ font-size:2rem;font-weight:700;font-variant-numeric:tabular-nums;line-height:1; }

        /* ── Glow line ── */
        .glow-line { height:1px;background:linear-gradient(90deg,transparent,rgba(0,120,212,.5) 40%,rgba(212,175,55,.4) 60%,transparent); }

        /* ── Hero gradient text ── */
        .hero-grad {
            background: linear-gradient(135deg, #0078D4 0%, #4BA3E3 50%, #D4AF37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ── Mock stat label colours ── */
        .msl-blue  { color:#0078D4; }
        .msl-gold  { color:#D4AF37; }
        .msl-green { color:#10b981; }
        .msl-value { color:white; font-size:16px; font-weight:700; font-variant-numeric:tabular-nums; line-height:1; }
        .msl-sub   { color:rgba(148,163,184,.6); font-size:8px; margin-top:2px; }
        .msl-label { font-size:8px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; margin-bottom:3px; }

        /* ── Mock row internals ── */
        .mrow-time  { width:38px; text-align:right; font-size:9px; color:rgba(148,163,184,.6); font-variant-numeric:tabular-nums; flex-shrink:0; }
        .mrow-name  { font-size:9px; color:white; font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .mrow-svc   { font-size:8px; color:rgba(148,163,184,.5); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .mrow-flex  { flex:1; min-width:0; }

        /* ── Mock booking badge colours ── */
        .mbadge-green { background:rgba(16,185,129,.12);  color:#10b981; border:1px solid rgba(16,185,129,.25); }
        .mbadge-amber { background:rgba(245,158,11,.12);  color:#f59e0b; border:1px solid rgba(245,158,11,.25); }
        .mbadge-blue  { background:rgba(0,120,212,.12);   color:#0078D4; border:1px solid rgba(0,120,212,.25);  }

        /* ── Feature card icon colours ── */
        .ficon-blue   { background:rgba(0,120,212,.12);   border:1px solid rgba(0,120,212,.16);   color:#0078D4; }
        .ficon-gold   { background:rgba(212,175,55,.1);   border:1px solid rgba(212,175,55,.16);  color:#D4AF37; }
        .ficon-green  { background:rgba(16,185,129,.1);   border:1px solid rgba(16,185,129,.16);  color:#10b981; }
        .ficon-purple { background:rgba(167,139,250,.1);  border:1px solid rgba(167,139,250,.16); color:#a78bfa; }

        /* ── Background grid ── */
        .bg-grid {
            background-image: linear-gradient(rgba(0,120,212,.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(0,120,212,.03) 1px, transparent 1px);
            background-size: 56px 56px;
        }

        /* ── CTA section bottom glow ── */
        .cta-glow {
            background: radial-gradient(ellipse at 50% 100%, rgba(0,120,212,.1) 0%, transparent 60%);
        }
    </style>
</head>
<body class="antialiased bg-slate-950 text-slate-100">

{{-- ── NAV ───────────────────────────────────────────────────────────────── --}}
<header class="sticky top-0 z-50 bg-slate-950/90 backdrop-blur-md border-b border-slate-800/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">

            <a href="{{ route('welcome') }}" class="flex items-center gap-2.5 shrink-0">
                <img src="/img/android-icon-192x192.png" alt="Xquisite" class="h-8 w-auto object-contain rounded-lg">
                <div class="leading-none">
                    <p class="f-mont font-bold text-xs tracking-widest text-white">XQUISITE</p>
                    <p class="f-mont font-semibold text-[9px] tracking-[.2em] text-[#D4AF37]">CREATIONS</p>
                </div>
            </a>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('welcome') }}" class="text-sm text-slate-400 hover:text-slate-200 transition px-3 py-1.5">&larr; Home</a>
                <a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-slate-200 transition px-3 py-1.5">Log in</a>
                <a href="{{ route('register') }}"
                   class="px-4 py-2 text-sm font-semibold text-white bg-[#0078D4] hover:bg-[#0065B8] rounded-lg transition-colors whitespace-nowrap">
                    Get started free
                </a>
            </div>
        </div>
    </div>
</header>

{{-- ── HERO ────────────────────────────────────────────────────────────── --}}
<section class="relative overflow-hidden bg-slate-950 pt-16 pb-20 sm:pt-24 sm:pb-28">
    <div class="orb orb-blue"></div>
    <div class="orb orb-gold"></div>

    {{-- Subtle grid --}}
    <div class="absolute inset-0 pointer-events-none bg-grid"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid lg:grid-cols-2 gap-14 lg:gap-10 items-center">

            {{-- Left: copy --}}
            <div>
                {{-- Live badge --}}
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs font-semibold mb-8 xq-enter" style="--d:.05s">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" style="animation:pulse-dot 2s ease-in-out infinite"></span>
                    Live demo &mdash; full platform access
                </div>

                <h1 class="f-mont text-4xl sm:text-5xl xl:text-6xl font-bold leading-[1.1] mb-6 xq-enter" style="--d:.15s">
                    See it. Feel it.<br>
                    <span class="hero-grad">Run it live.</span>
                </h1>

                <p class="text-slate-400 text-base sm:text-lg leading-relaxed mb-10 max-w-lg xq-enter" style="--d:.25s">
                    The full Xquisite platform, loaded with demo data. Bookings, clients, POS, analytics — click anything, create anything. No setup needed.
                </p>

                {{-- CTA --}}
                <div class="flex flex-col sm:flex-row gap-3 mb-10 xq-enter" style="--d:.35s">
                    <form method="POST" action="{{ route('demo.login') }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="btn-shimmer inline-flex items-center gap-2.5 px-8 py-4 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl shadow-lg shadow-[#0078D4]/20 transition-colors text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
                            </svg>
                            Launch demo
                        </button>
                    </form>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-1.5 px-7 py-4 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-500 rounded-xl transition-colors text-sm font-medium">
                        Create free account &rarr;
                    </a>
                </div>

                {{-- Trust signals --}}
                <div class="flex flex-wrap gap-x-6 gap-y-3 xq-enter" style="--d:.45s">
                    @foreach([
                        ['No sign-up required',    'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                        ['Full admin access',       'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z'],
                        ['Resets every 6 hours',   'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99'],
                    ] as [$text, $icon])
                    <div class="flex items-center gap-1.5 text-sm text-slate-400">
                        <svg class="w-4 h-4 text-[#0078D4] shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                        </svg>
                        {{ $text }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Right: dashboard mockup --}}
            <div class="hidden lg:block relative xq-enter" style="--d:.2s">
                {{-- Outer glow --}}
                <div class="absolute -inset-4 rounded-2xl pointer-events-none" style="background:radial-gradient(ellipse at 60% 40%,rgba(0,120,212,.14) 0%,transparent 65%);"></div>

                <div class="mock-chrome relative z-10">
                    {{-- Browser bar --}}
                    <div class="mock-bar">
                        <div class="mock-dot bg-red-400/60"></div>
                        <div class="mock-dot bg-amber-400/60"></div>
                        <div class="mock-dot bg-emerald-400/60"></div>
                        <div class="mock-addr">
                            <svg class="w-3 h-3 text-slate-600 mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25z"/></svg>
                            <span class="text-slate-500 text-[10px] font-mono">xquisite.co.za/dashboard</span>
                        </div>
                        <div class="flex items-center gap-1.5 ml-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" style="animation:pulse-dot 2s ease-in-out infinite"></span>
                            <span class="text-[9px] text-emerald-400/70 font-medium">Live</span>
                        </div>
                    </div>

                    {{-- App shell --}}
                    <div class="flex" style="min-height:390px;background:#0f172a;">
                        {{-- Sidebar --}}
                        <div class="mock-sidebar">
                            <div class="px-3 py-2 mb-1">
                                <p class="text-[8px] font-bold uppercase tracking-widest text-slate-500">Demo Salon</p>
                            </div>
                            @foreach([
                                ['Dashboard',  'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6', true],
                                ['Bookings',   'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', false],
                                ['Clients',    'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197', false],
                                ['Services',   'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.658.43l5.096-2.548c.878-.439 1.23-1.517.804-2.412l-.75-1.605', false],
                                ['Staff',      'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0', false],
                                ['Analytics',  'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', false],
                                ['POS',        'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z', false],
                            ] as [$label, $icon, $active])
                            <div class="mock-nav {{ $active ? 'on' : '' }}">
                                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                                </svg>
                                {{ $label }}
                            </div>
                            @endforeach
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 p-4 overflow-hidden">
                            <p class="text-[9px] font-bold uppercase tracking-widest text-slate-500 mb-3">Today's Overview</p>

                            <div class="grid grid-cols-3 gap-2 mb-4">
                                @foreach([['Bookings','12','+3','msl-blue'],['Revenue','R4 280','month','msl-gold'],['Clients','47','8 new','msl-green']] as [$l,$v,$s,$cls])
                                <div class="mock-stat">
                                    <p class="msl-label {{ $cls }}">{{ $l }}</p>
                                    <p class="msl-value">{{ $v }}</p>
                                    <p class="msl-sub">{{ $s }}</p>
                                </div>
                                @endforeach
                            </div>

                            <p class="text-[8px] font-bold uppercase tracking-widest text-slate-500 mb-2">Upcoming appointments</p>
                            @foreach([['10:00','Sarah M.','Balayage + Trim','mbadge-green'],['11:30','Zanele K.','Nail Extensions','mbadge-amber'],['14:00','Priya N.','Skin Treatment','mbadge-green'],['15:30','Mpho D.','Hair & Blowout','mbadge-blue']] as [$t,$c,$s,$cls])
                            <div class="mock-row">
                                <div class="mrow-time">{{ $t }}</div>
                                <div class="mrow-flex">
                                    <p class="mrow-name">{{ $c }}</p>
                                    <p class="mrow-svc">{{ $s }}</p>
                                </div>
                                <span class="mock-badge {{ $cls }}">confirmed</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Floating badge --}}
                <div class="absolute -bottom-3 -right-3 z-20 flex items-center gap-2 bg-slate-800 border border-slate-700 text-xs font-medium px-3 py-2 rounded-xl shadow-xl text-slate-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400" style="animation:pulse-dot 2s ease-in-out infinite"></span>
                    Real-time data
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── GLOW DIVIDER ─────────────────────────────────────────────────────── --}}
<div class="glow-line"></div>

{{-- ── STATS STRIP ──────────────────────────────────────────────────────── --}}
<section class="bg-slate-900/60 py-10 border-y border-slate-800/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-10 text-center">
            @foreach([
                ['7',    'Platform modules', 'text-[#0078D4]'],
                ['100%', 'Admin access',     'text-[#D4AF37]'],
                ['Live', 'Real data',        'text-[#10b981]'],
                ['0',    'Setup required',   'text-[#a78bfa]'],
            ] as [$val, $label, $cls])
            <div class="sr d{{ $loop->iteration }}">
                <div class="stat-value mb-1 {{ $cls }}">{{ $val }}</div>
                <p class="text-sm text-slate-400">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── WHAT'S INSIDE ────────────────────────────────────────────────────── --}}
<section class="py-20 sm:py-28 bg-slate-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section header --}}
        <div class="text-center mb-14 sr">
            <p class="f-mont text-xs font-bold uppercase tracking-[.18em] text-[#D4AF37] mb-3">Full platform access</p>
            <h2 class="f-mont text-3xl sm:text-4xl font-bold text-white mb-4">What's inside the demo</h2>
            <p class="text-slate-400 text-base max-w-xl mx-auto leading-relaxed">
                Every module is live. Create bookings, ring up a sale, browse analytics — it's the real platform.
            </p>
        </div>

        {{-- Feature grid ── Creative Tim feature section pattern --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 lg:gap-6">
            @foreach([
                [
                    'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
                    'label' => 'Booking Management',
                    'desc'  => 'Create appointments, assign staff, manage services with categories and durations. See the calendar and conflict warnings in action.',
                    'cls'   => 'ficon-blue',
                ],
                [
                    'icon'  => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
                    'label' => 'Client Profiles',
                    'desc'  => 'View client history, total spend, appointment notes. Send WhatsApp messages directly from the client card.',
                    'cls'   => 'ficon-gold',
                ],
                [
                    'icon'  => 'M17 20h5v-2a3 3 0 0 0-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 0 1 5.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 0 1 9.288 0M15 7a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                    'label' => 'Staff & Schedules',
                    'desc'  => 'Set working hours, block unavailable slots, and see each staff member\'s day at a glance on the calendar.',
                    'cls'   => 'ficon-green',
                ],
                [
                    'icon'  => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z',
                    'label' => 'Point of Sale',
                    'desc'  => 'Full POS terminal — ring up products and services, apply discounts, split payments, and generate receipts.',
                    'cls'   => 'ficon-purple',
                ],
                [
                    'icon'  => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
                    'label' => 'Analytics & Reports',
                    'desc'  => 'Revenue by service, top clients, staff performance and booking trends — all in one live dashboard.',
                    'cls'   => 'ficon-blue',
                ],
                [
                    'icon'  => 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
                    'label' => 'Public Booking Portal',
                    'desc'  => 'Your clients see a branded booking page at their own URL — mobile-first, no app download needed.',
                    'cls'   => 'ficon-gold',
                ],
            ] as $f)
            <div class="ct-card p-6 sr d{{ min($loop->iteration, 6) }}">
                <div class="ct-icon mb-5 {{ $f['cls'] }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $f['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="f-mont font-bold text-white text-sm sm:text-base mb-2">{{ $f['label'] }}</h3>
                <p class="text-slate-400 text-sm leading-relaxed">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── GLOW DIVIDER ─────────────────────────────────────────────────────── --}}
<div class="glow-line"></div>

{{-- ── BOTTOM CTA ───────────────────────────────────────────────────────── --}}
<section class="bg-slate-900 py-20 sm:py-28 relative overflow-hidden">
    <div class="absolute inset-0 pointer-events-none cta-glow"></div>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10 sr">
        <p class="f-mont text-xs font-bold uppercase tracking-[.18em] text-[#D4AF37] mb-4">No commitment</p>
        <h2 class="f-mont text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-5 leading-tight">
            Ready to see it<br>for yourself?
        </h2>
        <p class="text-slate-400 text-base leading-relaxed mb-10 max-w-lg mx-auto">
            One click and you're inside the full platform. No account, no credit card, no setup. Demo data resets every 6 hours.
        </p>

        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <form method="POST" action="{{ route('demo.login') }}">
                @csrf
                <button type="submit"
                        class="btn-shimmer inline-flex items-center gap-2.5 px-10 py-4 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl shadow-lg shadow-[#0078D4]/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/>
                    </svg>
                    Launch demo now
                </button>
            </form>
            <a href="{{ route('register') }}"
               class="inline-flex items-center gap-1.5 px-8 py-4 text-slate-300 hover:text-white border border-slate-700 hover:border-[#D4AF37] hover:text-[#D4AF37] rounded-xl transition-colors text-sm font-medium">
                Create free account &rarr;
            </a>
        </div>

        <p class="mt-10 text-xs text-slate-600">Xquisite Creations &mdash; Built for South African Business.</p>
    </div>
</section>

<script>
(function () {
    var els = document.querySelectorAll('.sr');
    if (!('IntersectionObserver' in window)) { els.forEach(function(e){e.classList.add('in');}); return; }
    var io = new IntersectionObserver(function(entries){
        entries.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
    els.forEach(function(e){ io.observe(e); });
})();
</script>
</body>
</html>
