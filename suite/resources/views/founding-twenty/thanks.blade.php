<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Received — Xquisite Creations Founding 20</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="w-9 h-9 rounded-lg object-contain shrink-0">
        <span class="text-lg font-bold text-slate-900">Xquisite Creations</span>
    </div>
</header>

<main class="max-w-md mx-auto px-4 py-20 text-center">
    <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center mx-auto mb-5">
        <svg class="w-7 h-7 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    </div>
    <h1 class="text-xl font-bold text-slate-900">Thank you</h1>
    <p class="text-slate-500 text-sm mt-2">
        We've received your answers. If you're a fit for the Founding 20 Programme, we'll reach out via your preferred
        contact method to get you set up — 3 months free, no setup fee.
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
