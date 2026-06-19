<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Xquisite Creations') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:500,600,700,800|inter:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="apple-touch-icon" sizes="57x57" href="/img/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/img/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/img/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/img/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/img/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/img/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
    <link rel="manifest" href="/img/manifest.json">
    <meta name="msapplication-TileColor" content="#002B5B">
    <meta name="msapplication-TileImage" content="/img/ms-icon-144x144.png">
    <meta name="theme-color" content="#002B5B">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .f-mont { font-family: 'Montserrat', sans-serif; }
    </style>
</head>

<body class="antialiased bg-[#F5F7FA] text-[#2D3748]">
<div class="min-h-screen flex">

    <!-- Left: Brand panel (hidden on mobile) -->
    <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 relative bg-[#002B5B] flex-col justify-between p-12 overflow-hidden">

        <!-- Subtle background accents -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-[#0078D4]/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-80 h-80 bg-[#D4AF37]/5 rounded-full blur-3xl"></div>
        </div>

        <!-- Logo -->
        <div class="relative">
            <a href="/" class="flex items-center gap-3">
                <img src="/img/android-icon-192x192.png" alt="Xquisite" class="h-10 w-auto object-contain rounded-xl shrink-0">
                <div class="leading-none">
                    <p class="f-mont font-bold text-base tracking-wide text-white">XQUISITE</p>
                    <p class="f-mont font-semibold text-[10px] tracking-widest text-[#D4AF37]">CREATIONS</p>
                </div>
            </a>
        </div>

        <!-- Center content -->
        <div class="relative space-y-10">
            <div>
                <div class="w-12 h-0.5 bg-[#D4AF37] mb-6 rounded-full"></div>
                <h2 class="f-mont text-3xl font-bold text-white leading-snug">
                    Business solutions<br>
                    <span class="text-[#0078D4]">built around you.</span>
                </h2>
                <p class="mt-4 text-[#B8D4F0] leading-relaxed text-sm">
                    Custom software, automation, data analytics, and digital platforms — one platform for your entire operation.
                </p>
                <p class="mt-3 f-mont font-semibold italic text-[#D4AF37] text-sm">Understand Your Why.</p>
            </div>

            <ul class="space-y-4">
                @foreach([
                    ['Business Technology', 'Custom software, ERP & POS built for your workflow'],
                    ['Automation',          'Eliminate repetitive work across your operations'],
                    ['Data & Intelligence', 'Dashboards and analytics that drive better decisions'],
                    ['Digital Presence',    'Websites, e-commerce and booking systems that convert'],
                ] as [$title, $desc])
                <li class="flex items-start gap-3">
                    <div class="mt-0.5 w-5 h-5 bg-[#D4AF37]/20 border border-[#D4AF37]/40 rounded-md flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-[#D4AF37]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-white">{{ $title }}</p>
                        <p class="text-xs text-[#B8D4F0]/70 mt-0.5">{{ $desc }}</p>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Footer -->
        <div class="relative text-xs text-white/25">
            &copy; {{ date('Y') }} Xquisite Creations (Pty) Ltd. All rights reserved.
        </div>
    </div>

    <!-- Right: Form panel -->
    <div class="flex-1 flex flex-col items-center justify-center px-4 py-8 sm:px-8 sm:py-12 lg:px-12">

        <!-- Mobile logo -->
        <div class="lg:hidden mb-6 sm:mb-8 text-center">
            <a href="/" class="inline-flex items-center gap-2 justify-center">
                <img src="/img/android-icon-192x192.png" alt="Xquisite" class="h-9 w-auto object-contain rounded-lg shrink-0">
                <div class="text-left leading-none">
                    <p class="f-mont font-bold text-sm tracking-wide text-[#002B5B]">XQUISITE</p>
                    <p class="f-mont font-semibold text-[10px] tracking-widest text-[#D4AF37]">CREATIONS</p>
                </div>
            </a>
        </div>

        <!-- Form slot -->
        <div class="w-full max-w-md">
            {{ $slot }}
        </div>
    </div>

</div>
    <script>
    /* ── Form submit guard: prevents double-submit on slow networks ── */
    (function () {
        var SPINNER = '<svg class="inline animate-spin w-3.5 h-3.5 mr-1.5 -mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>';

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (form.dataset.xqBusy) { e.preventDefault(); return; }
            form.dataset.xqBusy = '1';
            var btn = form.querySelector('[type="submit"]');
            if (!btn) return;
            btn.disabled = true;
            btn.dataset.xqOrig = btn.innerHTML;
            btn.innerHTML = SPINNER + (btn.dataset.loading || 'Please wait…');
        }, true);

        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            document.querySelectorAll('[data-xq-busy]').forEach(function (form) {
                delete form.dataset.xqBusy;
                var btn = form.querySelector('[type="submit"]');
                if (btn && btn.dataset.xqOrig) { btn.disabled = false; btn.innerHTML = btn.dataset.xqOrig; }
            });
        });
    })();
    </script>
</body>
</html>
