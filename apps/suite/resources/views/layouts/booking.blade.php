<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} — Book an Appointment</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
        <div>
            <a href="{{ route('book.index', $slug) }}" class="text-xl font-bold text-slate-900 hover:text-indigo-600 transition">
                {{ $tenant->name }}
            </a>
            <span class="ml-2 text-xs text-slate-400 font-medium uppercase tracking-wide">Book</span>
        </div>
        <div class="flex items-center gap-3 text-sm">
            @auth('customer')
                @php
                    $customer = auth('customer')->user();
                    $customerUnread = $customer ? $customer->unreadNotifications()->count() : 0;
                @endphp
                <a href="{{ route('book.notifications', $slug) }}" class="text-slate-600 hover:text-indigo-600 flex items-center gap-1">
                    Notifications
                    @if($customerUnread > 0)
                        <span class="inline-flex items-center justify-center px-2 py-0.5 text-[11px] font-semibold leading-none text-white bg-red-500 rounded-full">
                            {{ $customerUnread }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('book.my-bookings', $slug) }}" class="text-slate-600 hover:text-indigo-600">
                    My Bookings
                </a>
                <span class="text-slate-300">|</span>
                <form method="POST" action="{{ route('book.logout', $slug) }}" class="inline">
                    @csrf
                    <button class="text-slate-400 hover:text-slate-700">Sign out</button>
                </form>
            @else
                <a href="{{ route('book.login', $slug) }}" class="text-slate-600 hover:text-indigo-600">Sign in</a>
                <a href="{{ route('book.register', $slug) }}"
                   class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-1.5 rounded-lg text-sm font-medium transition">
                    Create account
                </a>
            @endauth
        </div>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 py-10">
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 text-blue-700 rounded-xl text-sm">
            {{ session('info') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="space-y-1">
                @foreach($errors->all() as $e) <li>• {{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>

<footer class="border-t border-slate-200 mt-20 py-6 text-center text-xs text-slate-400">
    Powered by <span class="font-semibold text-slate-500">Xquisite</span>
</footer>

@stack('scripts')
</body>
</html>
