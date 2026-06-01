<x-guest-layout>
<div class="min-h-screen bg-gray-950 text-gray-100">

    <!-- NAV -->
    <nav class="border-b border-gray-800/60 bg-gray-950/80 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">

                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600/10 flex items-center justify-center">
                        <x-application-logo class="h-6 w-auto fill-current text-indigo-400" />
                    </div>
                    <span class="text-lg font-semibold tracking-wide text-white">
                        Xquisite
                    </span>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}"
                       class="text-sm text-gray-400 hover:text-white transition">
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                           class="px-4 py-2 text-sm bg-indigo-600 hover:bg-indigo-500 rounded-lg font-medium shadow-sm shadow-indigo-600/20">
                            Get Started
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="max-w-6xl mx-auto px-6 lg:px-8 pt-24 pb-16 text-center">

        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-xs tracking-wide mb-6">
            Xquisite Suite &mdash; Modular Business Operating System
        </div>

        <h1 class="text-4xl md:text-6xl font-bold leading-tight">
            Run everything.
        </h1>

        <p class="mt-4 text-xl text-indigo-300 font-medium">One platform. Every operation.</p>

        <p class="mt-5 text-gray-400 text-lg max-w-2xl mx-auto leading-relaxed">
            Bookings, POS, inventory, e-commerce, and property management — unified into one modular platform built for South African business.
        </p>

        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}"
               class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-semibold shadow-lg shadow-indigo-600/20">
                Start Free Trial
            </a>

            <a href="{{ route('login') }}"
               class="px-6 py-3 bg-gray-900 border border-gray-800 hover:border-gray-700 rounded-xl text-gray-300 hover:text-white">
                View Demo
            </a>
        </div>
    </section>

    <!-- VALUE PROP STRIP -->
    <section class="max-w-6xl mx-auto px-6 lg:px-8 pb-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">

            <div class="p-6 rounded-xl bg-gray-900 border border-gray-800">
                <h3 class="font-semibold mb-2">Fully Modular</h3>
                <p class="text-sm text-gray-400">Activate only what your business needs. Nothing extra.</p>
            </div>

            <div class="p-6 rounded-xl bg-gray-900 border border-gray-800">
                <h3 class="font-semibold mb-2">Real-time Sync</h3>
                <p class="text-sm text-gray-400">Inventory, POS, and bookings stay instantly aligned.</p>
            </div>

            <div class="p-6 rounded-xl bg-gray-900 border border-gray-800">
                <h3 class="font-semibold mb-2">Multi-tenant Core</h3>
                <p class="text-sm text-gray-400">Each business runs in a fully isolated environment.</p>
            </div>

        </div>
    </section>

    <!-- MODULES -->
    <section class="max-w-7xl mx-auto px-6 lg:px-8 pb-20">

        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold">Core Business Modules</h2>
            <p class="text-gray-400 mt-3">One system. Multiple operational layers.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach([
                ['Supplier Management','Users, POs, vendor tracking'],
                ['Inventory Control','Live stock with automated updates'],
                ['Point of Sale','Fast checkout with inventory sync'],
                ['Bookings Engine','Appointments & scheduling system'],
                ['E-commerce','Online storefront connected to stock'],
                ['Analytics','Unified business insights']
            ] as $m)

            <div class="p-6 rounded-xl bg-gray-900 border border-gray-800 hover:border-indigo-500/40 transition">
                <h3 class="font-semibold mb-2">{{ $m[0] }}</h3>
                <p class="text-sm text-gray-400">{{ $m[1] }}</p>
            </div>

            @endforeach

        </div>
    </section>

    <!-- TESTIMONIALS -->
    @php $testimonials = \App\Models\Review::public()->limit(6)->get(); @endphp
    @if ($testimonials->isNotEmpty())
    <section class="max-w-7xl mx-auto px-6 lg:px-8 pb-24">

        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold">Businesses running on Xquisite</h2>
            <p class="text-gray-400 mt-3">Real feedback from real operators.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($testimonials as $review)
            <div class="p-6 rounded-2xl bg-gray-900 border {{ $review->is_featured ? 'border-indigo-500/40' : 'border-gray-800' }}">
                <div class="flex items-center gap-0.5 mb-3">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-700' }}"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    @endfor
                </div>

                @if ($review->title)
                    <p class="font-semibold text-white text-sm mb-2">{{ $review->title }}</p>
                @endif

                <p class="text-gray-400 text-sm leading-relaxed line-clamp-4">{{ $review->body }}</p>

                <div class="mt-4 pt-4 border-t border-gray-800 flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-indigo-600/20 flex items-center justify-center text-indigo-400 text-xs font-bold shrink-0">
                        {{ strtoupper(substr($review->display_name ?? 'B', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-300">{{ $review->display_name ?? 'Business Owner' }}</p>
                        @if ($review->business_type)
                            <p class="text-xs text-gray-500">{{ $review->business_type }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </section>
    @endif

    <!-- CTA -->
    <section class="border-t border-gray-800">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 py-20 text-center">

            <h2 class="text-3xl md:text-4xl font-bold">
                Build your system once.
                <span class="text-indigo-400">Run everything.</span>
            </h2>

            <p class="text-gray-400 mt-3 text-lg">One platform. Every operation.</p>

            <p class="text-gray-500 mt-3 text-sm">
                Xquisite Technologies (Pty) Ltd &mdash; Built for South African business.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}"
                   class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-semibold">
                    Get Started
                </a>
                <a href="{{ route('login') }}"
                   class="px-6 py-3 bg-gray-900 border border-gray-800 rounded-xl text-gray-300">
                    Login
                </a>
            </div>

        </div>
    </section>

</div>
</x-guest-layout>