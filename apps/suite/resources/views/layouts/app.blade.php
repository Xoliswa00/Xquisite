```blade id="l2m9xq"
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Xquisite Suite</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-900 text-slate-100">

<div class="min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-slate-800 p-6 hidden md:block">
        <h1 class="text-2xl font-bold mb-8">Xquisite</h1>

        <nav class="space-y-2 text-sm">

            <a href="{{ route('dashboard') }}"
               class="block px-4 py-2 rounded hover:bg-slate-700">
                Dashboard
            </a>

            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">
                Bookings
            </a>

            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">
                POS
            </a>

            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">
                Inventory
            </a>

            <a href="#" class="block px-4 py-2 rounded hover:bg-slate-700">
                Analytics
            </a>

        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">

        <!-- Top Bar -->
        <header class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
            <div class="font-semibold">
                {{ config('app.name', 'Xquisite Suite') }}
            </div>

            <div>
                {{ Auth::user()->name ?? 'User' }}
            </div>
        </header>

        <!-- Page Heading -->
        @isset($header)
            <div class="px-6 py-4 border-b border-slate-800">
                {{ $header }}
            </div>
        @endisset

        <!-- Page Content -->
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>

    </div>
</div>

</body>
</html>
```
