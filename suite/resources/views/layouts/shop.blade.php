<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? ($tenant->name . ' — Shop') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet"/>
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
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

<header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">

        <a href="{{ route('shop.index', $tenant->slug) }}" class="flex items-center gap-2 shrink-0 min-w-0">
            @if($tenant->logo_url)
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-8 w-auto object-contain shrink-0">
            @else
                <div class="w-8 h-8 bg-[#0078D4] rounded-lg flex items-center justify-center shrink-0">
                    <span class="text-white font-bold text-sm">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                </div>
            @endif
            <span class="font-semibold text-gray-900 truncate">{{ $tenant->name }}</span>
        </a>

        <div class="flex items-center gap-3">
            <!-- Search (desktop) -->
            <form action="{{ route('shop.index', $tenant->slug) }}" method="GET" class="hidden sm:block">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search products…"
                       class="bg-gray-100 border-0 text-sm rounded-full px-4 py-2 w-44 focus:w-60 focus:ring-1 focus:ring-[#0078D4] focus:outline-none transition-all placeholder-gray-400">
            </form>

            <!-- Cart -->
            <a href="{{ route('shop.cart', $tenant->slug) }}" class="relative flex items-center gap-1.5 text-sm font-medium text-gray-700 hover:text-[#0078D4]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @if(isset($cart) && $cart->count() > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-[#0078D4] text-white text-xs font-bold rounded-full flex items-center justify-center leading-none">
                        {{ $cart->count() }}
                    </span>
                @endif
                <span class="hidden sm:inline">Cart</span>
            </a>
        </div>
    </div>

    <!-- Mobile search row -->
    <div class="sm:hidden border-t border-gray-100 px-4 py-2">
        <form action="{{ route('shop.index', $tenant->slug) }}" method="GET">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search products…"
                   class="w-full bg-gray-100 border-0 text-sm rounded-full px-4 py-2 focus:ring-1 focus:ring-[#0078D4] focus:outline-none placeholder-gray-400">
        </form>
    </div>
</header>

<main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    @if(session('cart_success'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm">
            {{ session('cart_success') }}
        </div>
    @endif
    @if(session('cart_error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
            {{ session('cart_error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-sm">
            {{ session('info') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{ $slot }}
</main>

<footer class="border-t border-gray-200 mt-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-400">
        <span>© {{ date('Y') }} {{ $tenant->name }}</span>
        <a href="/" target="_blank" rel="noopener" class="hover:opacity-80 transition-opacity">Powered by <span class="text-[#0078D4] font-medium">Xquisite Suite</span></a>
    </div>
</footer>

</body>
</html>
