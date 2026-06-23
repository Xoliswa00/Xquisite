<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $tenant->name . ' — Book')</title>
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
        [x-cloak] { display: none !important; }
        body { background: linear-gradient(160deg, #f8faff 0%, #ffffff 40%, #f5f3ff 100%); min-height: 100vh; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="font-sans antialiased text-slate-800">

{{-- Header --}}
<header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-100 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
        <a href="{{ route('book.index', $slug) }}" class="flex items-center gap-3 min-w-0">
            @if(!empty($tenant->logo_url))
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="w-8 h-8 rounded-lg object-cover shrink-0">
            @else
                <span class="w-8 h-8 rounded-lg bg-[#0078D4] flex items-center justify-center text-white font-black text-sm shrink-0">
                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                </span>
            @endif
            <span class="font-bold text-slate-900 truncate text-sm sm:text-base">{{ $tenant->name }}</span>
        </a>

        <nav class="flex items-center gap-1 sm:gap-3 text-sm">
            @auth('customer')
                @php $customer = auth('customer')->user(); $unread = $customer?->unreadNotifications()->count(); @endphp
                <a href="{{ route('book.notifications', $slug) }}" class="relative flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="hidden sm:inline">Notifications</span>
                    @if($unread > 0)
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">{{ $unread }}</span>
                    @endif
                </a>
                <a href="{{ route('book.my-bookings', $slug) }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span class="hidden sm:inline">My Bookings</span>
                </a>
                <form method="POST" action="{{ route('book.logout', $slug) }}" class="inline">
                    @csrf
                    <button class="px-3 py-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition text-xs sm:text-sm">Sign out</button>
                </form>
            @else
                <a href="{{ route('book.login', $slug) }}" class="px-3 py-1.5 rounded-lg text-slate-600 hover:bg-slate-100 transition">Sign in</a>
                <a href="{{ route('book.register', $slug) }}" class="bg-[#0078D4] hover:bg-[#0078D4] text-white px-4 py-1.5 rounded-lg text-sm font-semibold transition shadow-sm">
                    Register
                </a>
            @endauth
        </nav>
    </div>
</header>

{{-- Alerts --}}
<div class="max-w-6xl mx-auto px-4 sm:px-6 pt-4 space-y-3">
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('info'))
        <div class="flex items-center gap-3 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-2xl text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('info') }}
        </div>
    @endif
    @if($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm space-y-1">
            @foreach($errors->all() as $e)<div class="flex items-start gap-2"><span class="shrink-0 mt-0.5">•</span>{{ $e }}</div>@endforeach
        </div>
    @endif
</div>

<main class="max-w-6xl mx-auto px-4 sm:px-6 py-8 pb-32 lg:pb-12">
    @yield('content')
</main>

<footer class="border-t border-slate-100 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>

@stack('scripts')
    <script>
    window.onerror = function(msg, src, line, col, err) {
        navigator.sendBeacon('{{ route('js.error') }}', new Blob([JSON.stringify({
            _token: '{{ csrf_token() }}', message: String(msg).slice(0,500),
            source: src, line: line, col: col, url: location.href,
            stack: err ? String(err.stack).slice(0,2000) : null
        })], {type:'application/json'}));
    };
    window.addEventListener('unhandledrejection', function(e) {
        navigator.sendBeacon('{{ route('js.error') }}', new Blob([JSON.stringify({
            _token: '{{ csrf_token() }}', message: ('[Promise] ' + (e.reason?.message || String(e.reason))).slice(0,500),
            url: location.href, stack: e.reason?.stack ? String(e.reason.stack).slice(0,2000) : null
        })], {type:'application/json'}));
    });
    </script>
</body>
</html>
