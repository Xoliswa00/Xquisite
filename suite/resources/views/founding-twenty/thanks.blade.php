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

<main class="max-w-md mx-auto px-4 py-14 sm:py-20">
    <div class="text-center">
        <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center mx-auto mb-5">
            <svg class="w-7 h-7 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        </div>
        <h1 class="text-2xl font-bold text-slate-900">Thank you, your application is in</h1>
        <p class="text-slate-500 text-base mt-2">We've received your answers. Nothing has been charged.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6 mt-8">
        <h2 class="text-base font-semibold text-slate-800 mb-4">What happens next</h2>
        <ol class="space-y-4 text-sm text-slate-600">
            <li class="flex items-start gap-3">
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-bold shrink-0">1</span>
                <span>We read every application. Only 20 businesses are selected, so we take care over who fits best.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-bold shrink-0">2</span>
                <span>If you're selected, we'll contact you on the WhatsApp, phone or email you chose, with a link to hold your spot using the fully refundable R100 deposit.</span>
            </li>
            <li class="flex items-start gap-3">
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-slate-900 text-white text-xs font-bold shrink-0">3</span>
                <span>Then we set you up, and your 3 free months begin.</span>
            </li>
        </ol>
    </div>

    <div class="text-center mt-8 space-y-3">
        <p class="text-sm text-slate-500">Something you'd like to ask while you wait?</p>
        <a href="{{ route('founding-20.show') }}#questions" class="inline-flex items-center justify-center px-6 py-3 bg-white border border-slate-300 hover:border-[#0078D4] text-slate-700 font-semibold rounded-xl text-sm transition">
            Ask us a question
        </a>
        <p><a href="{{ url('/') }}" class="text-sm text-[#0078D4] hover:underline">Back to Xquisite Creations</a></p>
    </div>
</main>

<footer class="border-t border-slate-200 mt-10 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>
</body>
</html>
