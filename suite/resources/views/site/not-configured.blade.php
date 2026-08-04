<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $tenant->name }}</title>
    <link rel="shortcut icon" href="{{ asset('img/favicon-32x32.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased flex items-center justify-center px-4">
    <div class="max-w-md text-center">
        @if($tenant->logo_url)
            <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-12 w-auto mx-auto mb-6 object-contain">
        @endif
        <h1 class="text-xl font-semibold text-white">{{ $tenant->name }}</h1>
        <p class="mt-3 text-sm text-slate-400">
            This website is being set up. Please check back soon.
        </p>
    </div>
</body>
</html>
