<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Dynamic Title --}}
    <title>{{ config('app.name', 'Xquisite Creations') }} {{ isset($title) ? ' - ' . $title : '' }}</title>

    {{-- SEO & Meta --}}
    <meta name="description" content="Engineering high-performance ERPs, POS systems, and business automation infrastructure.">
    
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/5/w3.css">
    {{-- Fonts: Inter is the gold standard for Enterprise UI --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

<!-- Vite Assets -->
@vite(['resources/css/app.css', 'resources/js/app.js'])



@livewireStyles
@vite(['resources/css/app.css', 'resources/js/app.js'])


</head>
<body class="font-sans antialiased bg-white text-slate-900 selection:bg-brand-gold selection:text-slate-900">
    
    <div class="min-h-screen flex flex-col relative">
        
        {{-- Fixed Navigation --}}
        <header class="fixed top-0 w-full z-[100] transition-all duration-300" id="main-nav">
            <x-navigation-menu />
        </header>

        {{-- Page Content --}}
        <main class="flex-1 pt-16 lg:pt-20">
            {{ $slot }}
        </main>

        {{-- Enterprise Footer --}}
        <footer class="bg-[#0f172a] border-t border-white/5 py-12">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex flex-col md:flex-row justify-between items-center gap-8">
                    <div class="space-y-2">
                        <span class="text-white font-black text-xl tracking-tighter uppercase">
                            {{ config('app.name') }}<span class="text-brand-gold">.</span>
                        </span>
                        <p class="text-slate-500 text-xs">Infrastructure for the next generation of business.</p>
                    </div>
                    
                    <div class="flex gap-8 text-xs font-bold text-slate-400 uppercase tracking-widest">
                        <a href="#services" class="hover:text-brand-gold transition-colors">Services</a>
                        <a href="#contact" class="hover:text-brand-gold transition-colors">Contact</a>
                        <a href="#" class="hover:text-brand-gold transition-colors">Terms</a>
                    </div>

                    <div class="text-xs text-slate-500 font-medium">
                        &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
    @stack('scripts')

    {{-- Subtle Nav Background Script --}}
    <script>
        window.onscroll = function() {
            const nav = document.getElementById('main-nav');
            if (window.pageYOffset > 50) {
                nav.classList.add('bg-[#0f172a]/90', 'backdrop-blur-md', 'shadow-xl', 'border-b', 'border-white/5');
            } else {
                nav.classList.remove('bg-[#0f172a]/90', 'backdrop-blur-md', 'shadow-xl', 'border-b', 'border-white/5');
            }
        };
    </script>
</body>
</html>