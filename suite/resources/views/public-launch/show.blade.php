<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $launch->title }} — Xquisite Creations</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:500,600,700,800|inter:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <meta name="theme-color" content="#002B5B">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .f-mont { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="antialiased bg-white text-[#2D3748]">

{{-- NAV --}}
<header class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            <a href="/" class="flex items-center gap-2 shrink-0">
                <img src="/img/android-icon-192x192.png" alt="Xquisite" class="h-8 w-auto object-contain rounded-lg shrink-0">
                <div class="leading-none">
                    <p class="f-mont font-bold text-sm tracking-wide text-[#002B5B]">XQUISITE</p>
                    <p class="f-mont font-semibold text-[10px] tracking-widest text-[#D4AF37]">CREATIONS</p>
                </div>
            </a>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="text-sm text-[#2D3748] hover:text-[#002B5B] transition">Log in</a>
                <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-semibold text-white bg-[#0078D4] hover:bg-[#0065B8] rounded-lg transition-colors">Get Started</a>
            </div>
        </div>
    </div>
</header>

<main class="max-w-3xl mx-auto px-4 sm:px-6 py-12 sm:py-20 space-y-16">

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
            {{ session('success') }}
        </div>
    @endif

    {{-- Hero --}}
    <div class="text-center">
        <h1 class="f-mont text-3xl sm:text-5xl font-bold text-[#002B5B] mb-4">{{ $launch->title }}</h1>
        @if($launch->tagline)
            <p class="text-base sm:text-lg text-[#2D3748]/70 max-w-xl mx-auto">{{ $launch->tagline }}</p>
        @endif
    </div>

    {{-- Countdown --}}
    @if($launch->hasCountdown())
        <div x-data="publicLaunchCountdown('{{ $launch->launch_at->toIso8601String() }}')" x-init="tick(); setInterval(tick, 1000)"
             class="grid grid-cols-4 gap-3 sm:gap-6 max-w-lg mx-auto">
            <template x-for="unit in units" :key="unit.label">
                <div class="bg-[#002B5B] rounded-2xl py-4 sm:py-6 text-center">
                    <p class="f-mont text-2xl sm:text-4xl font-bold text-white" x-text="unit.value"></p>
                    <p class="text-[10px] sm:text-xs uppercase tracking-widest text-[#D4AF37] mt-1" x-text="unit.label"></p>
                </div>
            </template>
        </div>
    @else
        <p class="text-center f-mont text-sm uppercase tracking-widest text-[#0078D4] font-semibold">Launching soon</p>
    @endif

    {{-- Benefits --}}
    @if(!empty($launch->benefits))
        <div class="bg-[#F8FAFC] border border-gray-100 rounded-2xl p-6 sm:p-10">
            <h2 class="f-mont text-xl font-bold text-[#002B5B] mb-6 text-center">What's included</h2>
            <ul class="space-y-4 max-w-lg mx-auto">
                @foreach($launch->benefits as $benefit)
                    <li class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-[#0078D4] shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span class="text-sm sm:text-base text-[#2D3748]">{{ $benefit }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Q&A --}}
    @if($launch->qa_enabled)
        <div class="space-y-8">
            <h2 class="f-mont text-xl font-bold text-[#002B5B] text-center">Questions from the community</h2>

            @if($launch->publishedQuestions->isNotEmpty())
                <div class="space-y-5 max-w-xl mx-auto">
                    @foreach($launch->publishedQuestions as $q)
                        <div class="border-b border-gray-100 pb-5">
                            <p class="font-semibold text-[#002B5B] text-sm sm:text-base">{{ $q->question }}</p>
                            <p class="text-sm text-[#2D3748]/70 mt-1.5">{{ $q->answer }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-center text-sm text-[#2D3748]/50 max-w-xl mx-auto">No questions answered yet — be the first to ask.</p>
            @endif

            <form method="POST" action="{{ route('public-launch.questions.store', $launch->key) }}" class="max-w-xl mx-auto bg-[#F8FAFC] border border-gray-100 rounded-2xl p-6 space-y-4">
                @csrf
                <p class="text-sm font-semibold text-[#002B5B]">Have a question about the programme?</p>
                @error('question')<p class="text-red-600 text-xs">{{ $message }}</p>@enderror

                <textarea name="question" rows="3" required maxlength="2000" placeholder="Ask anything about how it works…"
                          class="w-full border-gray-200 rounded-xl text-sm focus:ring-[#0078D4] focus:border-[#0078D4]">{{ old('question') }}</textarea>

                <div class="grid sm:grid-cols-2 gap-3">
                    <input type="text" name="asker_name" value="{{ old('asker_name') }}" placeholder="Your name (optional)"
                           class="w-full border-gray-200 rounded-xl text-sm focus:ring-[#0078D4] focus:border-[#0078D4]">
                    <input type="email" name="asker_email" value="{{ old('asker_email') }}" placeholder="Email (optional — if you want a reply)"
                           class="w-full border-gray-200 rounded-xl text-sm focus:ring-[#0078D4] focus:border-[#0078D4]">
                </div>

                <button type="submit" class="w-full py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-xl transition">
                    Ask your question
                </button>
            </form>
        </div>
    @endif

</main>

<footer class="border-t border-gray-100 py-8 text-center text-xs text-[#2D3748]/50">
    &copy; {{ now()->year }} Xquisite Creations (Pty) Ltd
</footer>

<script>
function publicLaunchCountdown(target) {
    return {
        units: [
            { label: 'Days', value: 0 },
            { label: 'Hours', value: 0 },
            { label: 'Minutes', value: 0 },
            { label: 'Seconds', value: 0 },
        ],
        tick() {
            const diff = Math.max(0, new Date(target) - new Date());
            const days    = Math.floor(diff / 86400000);
            const hours   = Math.floor((diff % 86400000) / 3600000);
            const minutes = Math.floor((diff % 3600000) / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            this.units = [
                { label: 'Days', value: days },
                { label: 'Hours', value: hours },
                { label: 'Minutes', value: minutes },
                { label: 'Seconds', value: seconds },
            ];
        },
    };
}
</script>

</body>
</html>
