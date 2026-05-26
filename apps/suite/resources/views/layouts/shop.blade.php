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
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">

<header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">

        <a href="{{ route('shop.index', $tenant->slug) }}" class="flex items-center gap-2">
            @if($tenant->logo_url)
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-8 w-auto object-contain">
            @else
                <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <span class="text-white font-bold text-sm">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                </div>
            @endif
            <span class="font-semibold text-gray-900">{{ $tenant->name }}</span>
        </a>

        <div class="flex items-center gap-4">
            <!-- Search (desktop) -->
            <form action="{{ route('shop.index', $tenant->slug) }}" method="GET" class="hidden sm:block">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search products…"
                       class="bg-gray-100 border-0 text-sm rounded-full px-4 py-2 w-48 focus:w-64 focus:ring-1 focus:ring-indigo-400 focus:outline-none transition-all placeholder-gray-400">
            </form>

            <!-- Cart -->
            <a href="{{ route('shop.cart', $tenant->slug) }}" class="relative flex items-center gap-1.5 text-sm font-medium text-gray-700 hover:text-indigo-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                @if(isset($cart) && $cart->count() > 0)
                    <span class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-indigo-600 text-white text-xs font-bold rounded-full flex items-center justify-center leading-none">
                        {{ $cart->count() }}
                    </span>
                @endif
                <span class="hidden sm:inline">Cart</span>
            </a>
        </div>
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
        <span>Powered by <span class="text-indigo-500 font-medium">Xquisite Suite</span></span>
    </div>
</footer>

</body>
</html>
