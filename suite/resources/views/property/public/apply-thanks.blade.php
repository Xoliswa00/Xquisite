<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Received — {{ $tenant->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
        @if(!empty($tenant->logo_url))
            <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="w-9 h-9 rounded-lg object-cover shrink-0">
        @else
            <span class="w-9 h-9 rounded-lg bg-[#0078D4] flex items-center justify-center text-white font-black text-sm shrink-0">
                {{ strtoupper(substr($tenant->name, 0, 1)) }}
            </span>
        @endif
        <span class="text-lg font-bold text-slate-900">{{ $tenant->name }}</span>
    </div>
</header>

<main class="max-w-md mx-auto px-4 py-20 text-center">
    <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-5">
        <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="text-xl font-bold text-slate-900">Application Received</h1>
    <p class="text-slate-500 text-sm mt-2">
        Thank you for applying for {{ $property->name }}. We've received your details and documents, and someone will be in touch once your application has been reviewed.
    </p>
</main>

<footer class="border-t border-slate-200 mt-10 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>
</body>
</html>
