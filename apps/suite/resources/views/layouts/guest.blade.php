```blade id="auth_layout_01"
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Xquisite Suite') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-950 text-slate-100">

<div class="min-h-screen flex flex-col justify-center items-center px-6">

    <!-- Brand -->
    <div class="mb-8 text-center">
        <a href="/" class="text-2xl font-bold tracking-wide">
            Xquisite Suite
        </a>
        <p class="text-sm text-slate-400 mt-2">
            Your business. Handled.
        </p>
    </div>

    <!-- Auth Card -->
    <div class="w-full sm:max-w-md bg-slate-900 border border-slate-800 shadow-xl rounded-2xl p-6">

        {{ $slot }}

    </div>

</div>

</body>
</html>
```
