<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Us — Xquisite Creations</title>
    <meta name="description" content="Meet the team behind Xquisite Creations — building business technology solutions for South African entrepreneurs.">
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
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; }
        .f-mont { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="antialiased text-[#2D3748] bg-white">

{{-- NAV --}}
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20 gap-4">
            <a href="/" class="flex items-center gap-2 shrink-0">
                <img src="/img/android-icon-192x192.png" alt="Xquisite" class="h-9 lg:h-10 w-auto object-contain rounded-lg shrink-0">
                <div class="leading-none">
                    <p class="f-mont font-bold text-sm tracking-wide text-[#002B5B]">XQUISITE</p>
                    <p class="f-mont font-semibold text-[10px] tracking-widest text-[#D4AF37]">CREATIONS</p>
                </div>
            </a>

            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-[#2D3748]">
                <a href="/#services" class="hover:text-[#0078D4] transition-colors">Services</a>
                <a href="/#modules"  class="hover:text-[#0078D4] transition-colors">Platform</a>
                <a href="/about"     class="text-[#0078D4] border-b-2 border-[#0078D4] pb-0.5">About</a>
                <a href="{{ route('demo.login') }}" class="hover:text-[#0078D4] transition-colors">Live Demo</a>
            </nav>

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

{{-- HERO --}}
<section class="bg-[#002B5B] text-white py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="w-14 h-1 rounded-full bg-[#D4AF37] mb-8"></div>
        <h1 class="f-mont text-4xl sm:text-5xl font-bold leading-tight mb-4">
            The People Behind<br>
            <span class="text-[#D4AF37]">Xquisite Creations.</span>
        </h1>
        <p class="text-base sm:text-lg text-[#B8D4F0] max-w-2xl leading-relaxed">
            We're a South African technology company on a mission to give every entrepreneur
            the tools, data, and systems they need to build something that lasts.
        </p>
    </div>
</section>

{{-- STORY --}}
<section class="py-16 sm:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            <div>
                <div class="w-12 h-0.5 rounded-full bg-[#D4AF37] mb-6"></div>
                <h2 class="f-mont text-3xl sm:text-4xl font-bold text-[#002B5B] mb-6">Our Story</h2>
                <div class="space-y-4 text-[#2D3748]/75 leading-relaxed">
                    <p>
                        Xquisite Creations was founded with a clear purpose: South African businesses deserve
                        technology that actually fits the way they operate — not tools built for a different
                        market and retrofitted for ours.
                    </p>
                    <p>
                        We started by working directly with service businesses — salons, spas, and independent
                        operators — and quickly learned that most platforms left them patching together five
                        different apps just to manage a single day. Bookings in one place, sales in another,
                        client data somewhere else entirely.
                    </p>
                    <p>
                        So we built Xquisite — one platform where your entire operation lives. Activate only
                        what you need, expand as your business grows, and always have your data in one place.
                    </p>
                </div>
                <p class="f-mont font-semibold text-lg italic text-[#D4AF37] mt-8">Understand Your Why.</p>
            </div>

            {{-- Values --}}
            <div class="grid grid-cols-1 gap-5">
                @foreach([
                    ['title' => 'Business-First Thinking',   'body' => 'We start with your operation, not with features. Every module we build solves a real problem real businesses told us about.'],
                    ['title' => 'Transparent & Local',       'body' => 'Proudly South African. Rand-denominated pricing, POPIA-compliant, and support that speaks your language.'],
                    ['title' => 'Built to Scale With You',   'body' => 'Whether you\'re one chair in a studio or five branches across Gauteng — the platform grows with you.'],
                ] as $v)
                <div class="flex items-start gap-4 bg-[#F5F7FA] rounded-xl p-5">
                    <div class="w-2 h-2 rounded-full bg-[#D4AF37] mt-2 shrink-0"></div>
                    <div>
                        <h3 class="f-mont font-semibold text-sm text-[#002B5B] mb-1">{{ $v['title'] }}</h3>
                        <p class="text-sm text-[#2D3748]/65 leading-relaxed">{{ $v['body'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- TEAM --}}
@if ($team->isNotEmpty())
<section class="bg-[#F5F7FA] py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 lg:mb-16">
            <div class="w-12 h-0.5 mx-auto mb-5 rounded-full bg-[#D4AF37]"></div>
            <h2 class="f-mont text-3xl sm:text-4xl font-bold mb-4 text-[#002B5B]">Meet the Team</h2>
            <p class="text-sm sm:text-base text-[#2D3748]/60 max-w-xl mx-auto">
                The people who design, build, and support the platform every day.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
            @foreach ($team as $member)
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-md transition-all">
                {{-- Photo --}}
                <div class="h-52 bg-[#002B5B] flex items-center justify-center overflow-hidden">
                    @if ($member->photoUrl())
                        <img src="{{ $member->photoUrl() }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="flex flex-col items-center gap-2">
                            <span class="f-mont text-4xl font-bold text-[#D4AF37]">{{ $member->initials() }}</span>
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-5">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="f-mont font-bold text-base text-[#002B5B]">{{ $member->name }}</h3>
                            <p class="text-sm text-[#0078D4] font-medium mt-0.5">{{ $member->role }}</p>
                        </div>
                        @if ($member->linkedin_url)
                        <a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener"
                           class="shrink-0 w-8 h-8 rounded-lg bg-[#F0F7FF] flex items-center justify-center text-[#0078D4] hover:bg-[#0078D4] hover:text-white transition-colors mt-0.5">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        @endif
                    </div>

                    @if ($member->bio)
                    <p class="text-sm text-[#2D3748]/65 leading-relaxed mt-3">{{ $member->bio }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@else
<section class="bg-[#F5F7FA] py-14 sm:py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="w-12 h-0.5 mx-auto mb-5 rounded-full bg-[#D4AF37]"></div>
        <h2 class="f-mont text-3xl sm:text-4xl font-bold mb-4 text-[#002B5B]">The People Behind the Platform</h2>
        <p class="text-base text-[#2D3748]/60 leading-relaxed">
            A small, dedicated team building tools that help beauty and wellness businesses thrive —
            hands-on, responsive, and obsessed with making Xquisite work beautifully for you.
        </p>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="bg-[#002B5B] py-16 sm:py-24">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="w-14 h-0.5 mx-auto mb-8 rounded-full bg-[#D4AF37]"></div>
        <h2 class="f-mont text-3xl sm:text-4xl font-bold text-white mb-4">
            Ready to Work With Us?
        </h2>
        <p class="text-base text-[#B8D4F0] max-w-xl mx-auto mb-10 leading-relaxed">
            Start with a 14-day free trial — no credit card required. Or try the live demo first.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="px-8 py-4 text-white font-semibold bg-[#0078D4] hover:bg-[#0065B8] rounded-xl shadow-lg transition-colors">
                Get Started Free
            </a>
            <a href="{{ route('demo.login') }}" class="px-8 py-4 font-semibold text-white rounded-xl border border-white/25 hover:border-[#D4AF37] hover:text-[#D4AF37] transition-colors">
                Try Live Demo
            </a>
        </div>
        <p class="mt-8 text-xs text-white/30">Xquisite Creations (Pty) Ltd — Built for South African Business.</p>
    </div>
</section>

<x-whatsapp-button />
</body>
</html>
