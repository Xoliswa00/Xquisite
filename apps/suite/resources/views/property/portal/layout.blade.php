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
    <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
        <div>
            <span class="text-xl font-bold text-slate-900">{{ $tenant->name }}</span>
            <span class="ml-2 text-xs text-slate-400 font-medium uppercase tracking-wide">Renter Portal</span>
        </div>
        <div class="flex items-center gap-4 text-sm">
            @auth('renter')
                <a href="{{ route('rent.portal', $slug) }}" class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('rent.portal') ? 'font-semibold text-indigo-600' : '' }}">Home</a>
                <a href="{{ route('rent.lease', $slug) }}" class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('rent.lease') ? 'font-semibold text-indigo-600' : '' }}">My Lease</a>
                <a href="{{ route('rent.payments', $slug) }}" class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('rent.payments') ? 'font-semibold text-indigo-600' : '' }}">Payments</a>
                <a href="{{ route('rent.maintenance', $slug) }}" class="text-slate-600 hover:text-indigo-600 {{ request()->routeIs('rent.maintenance') ? 'font-semibold text-indigo-600' : '' }}">Maintenance</a>
                <form method="POST" action="{{ route('rent.logout', $slug) }}" class="inline">
                    @csrf
                    <button class="text-slate-400 hover:text-slate-700 text-sm">Sign out</button>
                </form>
            @endauth
        </div>
    </div>
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
    Powered by <span class="font-semibold text-slate-500">Xquisite</span>
</footer>
</body>
</html>
