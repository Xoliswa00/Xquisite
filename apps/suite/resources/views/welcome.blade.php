<x-guest-layout>
@php
    $all     = collect(config('modules'));
    $active  = $all->where('status', 'active');
    $beta    = $all->where('status', 'beta');
    $soon    = $all->where('status', 'coming_soon');

    $icons = [
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>',
        'pos'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>',
        'store'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72"/>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z"/>',
        'chart'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
        'widget'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253"/>',
        'domain'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>',
        'star'     => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/>',
        'users'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
        'map'      => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/>',
    ];
@endphp
<div class="min-h-screen bg-gray-950 text-gray-100">

    <!-- NAV -->
    <nav class="border-b border-gray-800/60 bg-gray-950/80 backdrop-blur sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600/10 flex items-center justify-center">
                        <x-application-logo class="h-6 w-auto fill-current text-indigo-400" />
                    </div>
                    <span class="text-lg font-semibold tracking-wide text-white">Xquisite</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="text-sm text-gray-400 hover:text-white transition">Log in</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-500 rounded-lg font-medium shadow-sm shadow-indigo-600/20">
                            Get Started
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="max-w-6xl mx-auto px-6 lg:px-8 pt-24 pb-16 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs tracking-wide mb-6">
            Modular Business Operating System
        </div>
        <h1 class="text-4xl md:text-6xl font-bold leading-tight">
            Run your business
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">end-to-end</span>
        </h1>
        <p class="mt-6 text-gray-400 text-lg max-w-2xl mx-auto leading-relaxed">
            Supplier management, inventory control, POS, bookings, and e-commerce — unified into one modular platform built for real operations.
        </p>
        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}"
               class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-semibold shadow-lg shadow-indigo-600/20">
                Start Free Trial
            </a>
            <a href="{{ route('login') }}"
               class="px-6 py-3 bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl text-gray-300 hover:text-white">
                View Demo
            </a>
        </div>
    </section>

    <!-- VALUE PROPS -->
    <section class="max-w-6xl mx-auto px-6 lg:px-8 pb-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
            <div class="p-6 rounded-xl bg-gray-900 border border-gray-800">
                <h3 class="font-semibold mb-2">Fully Modular</h3>
                <p class="text-sm text-gray-400">Activate only what your business needs. Nothing extra.</p>
            </div>
            <div class="p-6 rounded-xl bg-gray-900 border border-gray-800">
                <h3 class="font-semibold mb-2">Real-time Sync</h3>
                <p class="text-sm text-gray-400">Inventory, POS, and bookings stay instantly aligned.</p>
            </div>
            <div class="p-6 rounded-xl bg-gray-900 border border-gray-800">
                <h3 class="font-semibold mb-2">Multi-tenant Core</h3>
                <p class="text-sm text-gray-400">Each business runs in a fully isolated environment.</p>
            </div>
        </div>
    </section>

    <!-- MODULES SHOWCASE -->
    <section class="max-w-7xl mx-auto px-6 lg:px-8 pb-24" id="modules">

        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold">Platform Modules</h2>
            <p class="text-gray-400 mt-3 max-w-xl mx-auto">
                Everything you need to run your business — pick the modules that fit, add more as you grow.
            </p>
        </div>

        {{-- ── LIVE ─────────────────────────────────────────────────────────── --}}
        @if ($active->isNotEmpty())
        <div class="mb-14">
            <div class="flex items-center gap-3 mb-6">
                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live
                </span>
                <span class="text-gray-600 text-sm">Available now — activate from your settings</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($active as $key => $module)
                <div class="group p-6 rounded-xl bg-gray-900 border border-gray-800 hover:border-emerald-500/30 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            {!! $icons[$module['icon']] ?? $icons['chart'] !!}
                        </svg>
                    </div>
                    <h3 class="font-semibold text-white mb-1">{{ $module['name'] }}</h3>
                    <p class="text-sm text-gray-400 leading-relaxed">{{ $module['description'] }}</p>
                    <p class="mt-4 text-xs text-gray-500">
                        From <span class="text-gray-300 font-medium">R{{ number_format($module['price'], 0) }}/mo</span>
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── IN TESTING ───────────────────────────────────────────────────── --}}
        @if ($beta->isNotEmpty())
        <div class="mb-14">
            <div class="flex items-center gap-3 mb-6">
                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-400 text-xs font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    In Testing
                </span>
                <span class="text-gray-600 text-sm">Being tested — launching to all clients soon</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($beta as $key => $module)
                <div class="group p-6 rounded-xl bg-gray-900/60 border border-gray-800/60 hover:border-amber-500/30 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            {!! $icons[$module['icon']] ?? $icons['chart'] !!}
                        </svg>
                    </div>
                    <div class="flex items-start justify-between mb-1">
                        <h3 class="font-semibold text-white">{{ $module['name'] }}</h3>
                        <span class="ml-2 shrink-0 text-[10px] px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-400 border border-amber-500/20">Beta</span>
                    </div>
                    <p class="text-sm text-gray-400 leading-relaxed">{{ $module['description'] }}</p>
                    <p class="mt-4 text-xs text-gray-500">
                        From <span class="text-gray-300 font-medium">R{{ number_format($module['price'], 0) }}/mo</span>
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── COMING SOON ──────────────────────────────────────────────────── --}}
        @if ($soon->isNotEmpty())
        <div>
            <div class="flex items-center gap-3 mb-6">
                <span class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/25 text-indigo-400 text-xs font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                    Coming Soon
                </span>
                <span class="text-gray-600 text-sm">On the roadmap — request early access</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach ($soon as $key => $module)
                <div class="group p-6 rounded-xl bg-gray-900/40 border border-gray-800/40 hover:border-indigo-500/20 transition-colors">
                    <div class="w-10 h-10 rounded-lg bg-indigo-500/8 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5 text-indigo-400/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            {!! $icons[$module['icon']] ?? $icons['chart'] !!}
                        </svg>
                    </div>
                    <div class="flex items-start justify-between mb-1">
                        <h3 class="font-semibold text-gray-300">{{ $module['name'] }}</h3>
                        <span class="ml-2 shrink-0 text-[10px] px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-400/80 border border-indigo-500/15">Soon</span>
                    </div>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $module['description'] }}</p>
                    <p class="mt-4 text-xs text-gray-600">
                        Est. <span class="text-gray-500 font-medium">R{{ number_format($module['price'], 0) }}/mo</span>
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </section>

    <!-- CTA -->
    <section class="border-t border-gray-800">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 py-20 text-center">
            <h2 class="text-3xl md:text-4xl font-bold">
                Build your system once.
                <span class="text-indigo-400">Run everything.</span>
            </h2>
            <p class="text-gray-400 mt-4">
                No complexity. No fragmented tools. One platform for your entire operation.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}"
                   class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-semibold">
                    Get Started
                </a>
                <a href="{{ route('login') }}"
                   class="px-6 py-3 bg-gray-900 border border-gray-800 rounded-xl text-gray-300">
                    Login
                </a>
            </div>
        </div>
    </section>

</div>
</x-guest-layout>
