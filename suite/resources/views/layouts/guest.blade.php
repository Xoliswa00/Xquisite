<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Xquisite Suite') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-950 text-slate-100">
<div class="min-h-screen flex">

    <!-- Left: Brand panel (hidden on mobile) -->
    <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 relative bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-950 flex-col justify-between p-12 overflow-hidden">

        <!-- Background decoration -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-80 h-80 bg-purple-700/10 rounded-full blur-3xl"></div>
        </div>

        <!-- Logo -->
        <div class="relative">
            <a href="{{ route('welcome') ?? '/' }}" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold text-lg">X</span>
                </div>
                <span class="text-xl font-bold tracking-wide text-white">Xquisite Suite</span>
            </a>
        </div>

        <!-- Center content -->
        <div class="relative space-y-10">
            <div>
                <h2 class="text-3xl font-bold text-white leading-snug">
                    Everything your business needs,<br>
                    <span class="text-indigo-400">in one place.</span>
                </h2>
                <p class="mt-4 text-slate-400 leading-relaxed">
                    Bookings, POS, inventory, suppliers and analytics — built for salons, spas and wellness businesses.
                </p>
            </div>

            <ul class="space-y-4">
                @foreach([
                    ['Booking Engine', 'Appointments, staff scheduling & reminders'],
                    ['Point of Sale', 'Fast checkout with automatic stock sync'],
                    ['Inventory Control', 'Real-time stock with reorder alerts'],
                    ['Analytics', 'Revenue, bookings and product insights'],
                ] as [$title, $desc])
                <li class="flex items-start gap-3">
                    <div class="mt-0.5 w-5 h-5 bg-indigo-600/20 border border-indigo-500/30 rounded-md flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ $title }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $desc }}</p>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Footer -->
        <div class="relative text-xs text-slate-600">
            © {{ date('Y') }} Xquisite Suite. All rights reserved.
        </div>
    </div>

    <!-- Right: Form panel -->
    <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 lg:px-12">

        <!-- Mobile logo -->
        <div class="lg:hidden mb-8 text-center">
            <a href="/" class="flex items-center justify-center gap-2">
                <div class="w-9 h-9 bg-indigo-600 rounded-xl flex items-center justify-center">
                    <span class="text-white font-bold">X</span>
                </div>
                <span class="text-xl font-bold text-white">Xquisite Suite</span>
            </a>
        </div>

        <!-- Form slot -->
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>

</div>
</body>
</html>
