<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} — Renter Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="bg-white border-b border-slate-200 shadow-sm">
    {{-- Brand row: full width, never competes with nav for space --}}
    <div class="max-w-5xl mx-auto px-4 pt-4 pb-3 sm:py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3 min-w-0">
            @if(!empty($tenant->logo_url))
                <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="w-9 h-9 rounded-lg object-cover shrink-0">
            @else
                <span class="w-9 h-9 rounded-lg bg-[#0078D4] flex items-center justify-center text-white font-black text-sm shrink-0">
                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                </span>
            @endif
            <div class="min-w-0">
                <span class="block text-lg sm:text-xl font-bold text-slate-900 truncate">{{ $tenant->name }}</span>
                <span class="text-xs text-slate-400 font-medium uppercase tracking-wide">Renter Portal</span>
            </div>
        </div>
        {{-- Nav: single row, icon + label, sm and up only --}}
        <div class="hidden sm:flex items-center gap-1 text-sm shrink-0">
            @auth('renter')
                <a href="{{ route('rent.portal', $slug) }}" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('rent.portal') ? 'font-semibold text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-600 hover:text-[#0078D4] hover:bg-slate-50' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Home
                </a>
                <a href="{{ route('rent.lease', $slug) }}" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('rent.lease') ? 'font-semibold text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-600 hover:text-[#0078D4] hover:bg-slate-50' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    My Lease
                </a>
                <a href="{{ route('rent.payments', $slug) }}" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('rent.payments') ? 'font-semibold text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-600 hover:text-[#0078D4] hover:bg-slate-50' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Payments
                </a>
                <a href="{{ route('rent.maintenance', $slug) }}" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('rent.maintenance') ? 'font-semibold text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-600 hover:text-[#0078D4] hover:bg-slate-50' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Maintenance
                </a>
                @php $portalUnreadCount = Auth::guard('renter')->user()->unreadNotifications()->count(); @endphp
                <a href="{{ route('rent.notifications.index', $slug) }}" aria-label="Notifications" class="relative flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg whitespace-nowrap {{ request()->routeIs('rent.notifications.*') ? 'font-semibold text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-600 hover:text-[#0078D4] hover:bg-slate-50' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($portalUnreadCount > 0)
                        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-[#0078D4] rounded-full">{{ $portalUnreadCount }}</span>
                    @endif
                </a>
                <form method="POST" action="{{ route('rent.logout', $slug) }}" class="inline">
                    @csrf
                    <button class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg whitespace-nowrap text-slate-400 hover:text-slate-700 hover:bg-slate-50">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign out
                    </button>
                </form>
            @endauth
        </div>
    </div>
    {{-- Nav: icon-only bar, below sm only, own row so the brand name never truncates --}}
    @auth('renter')
        <div class="sm:hidden flex items-center justify-around border-t border-slate-100 px-2 py-1.5">
            <a href="{{ route('rent.portal', $slug) }}" aria-label="Home" class="p-2 rounded-lg {{ request()->routeIs('rent.portal') ? 'text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </a>
            <a href="{{ route('rent.lease', $slug) }}" aria-label="My Lease" class="p-2 rounded-lg {{ request()->routeIs('rent.lease') ? 'text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </a>
            <a href="{{ route('rent.payments', $slug) }}" aria-label="Payments" class="p-2 rounded-lg {{ request()->routeIs('rent.payments') ? 'text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </a>
            <a href="{{ route('rent.maintenance', $slug) }}" aria-label="Maintenance" class="p-2 rounded-lg {{ request()->routeIs('rent.maintenance') ? 'text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </a>
            <a href="{{ route('rent.notifications.index', $slug) }}" aria-label="Notifications" class="relative p-2 rounded-lg {{ request()->routeIs('rent.notifications.*') ? 'text-[#0078D4] bg-[#0078D4]/10' : 'text-slate-500' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                @if($portalUnreadCount > 0)
                    <span class="absolute top-1 right-1 inline-flex items-center justify-center w-3.5 h-3.5 text-[9px] font-bold text-white bg-[#0078D4] rounded-full">{{ $portalUnreadCount }}</span>
                @endif
            </a>
            <form method="POST" action="{{ route('rent.logout', $slug) }}">
                @csrf
                <button aria-label="Sign out" class="p-2 rounded-lg text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    @endauth
</header>

<main class="max-w-3xl mx-auto px-4 py-10">
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
        </div>
    @endif
    @yield('content')
</main>

<footer class="border-t border-slate-200 mt-20 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>
</body>
</html>
