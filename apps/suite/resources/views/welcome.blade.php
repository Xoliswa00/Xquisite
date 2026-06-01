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
            Modular Business Operating System
        </div>

        <h1 class="text-4xl md:text-6xl font-bold leading-tight">
            Run your business
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400">
                end-to-end
            </span>
        </h1>

        <p class="mt-6 text-gray-400 text-lg max-w-2xl mx-auto leading-relaxed">
            Supplier management, inventory control, POS, bookings, and e-commerce — unified into one modular platform built for real operations.
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

    <!-- PRICING -->
    <section class="max-w-7xl mx-auto px-6 lg:px-8 pb-24" id="pricing">
        @php $plans = \App\Models\Plan::active()->ordered()->with('planModules.platformModule')->get(); @endphp

        @if ($plans->isNotEmpty())
        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold">Simple, Transparent Pricing</h2>
            <p class="text-gray-400 mt-3">Bundle and save — or pick individual modules if you only need one thing.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
            @foreach ($plans as $plan)
            <div class="rounded-2xl border {{ $plan->is_featured ? 'border-indigo-500 ring-2 ring-indigo-500/20 bg-gray-900' : 'border-gray-800 bg-gray-900/60' }} overflow-hidden">

                @if ($plan->is_featured)
                <div class="bg-indigo-600 text-white text-xs font-semibold text-center py-1.5 tracking-wide">
                    MOST POPULAR
                </div>
                @endif

                <div class="p-7">
                    <h3 class="text-lg font-bold text-white">{{ $plan->name }}</h3>
                    @if ($plan->tagline)
                        <p class="text-sm text-gray-400 mt-1">{{ $plan->tagline }}</p>
                    @endif

                    <div class="mt-5">
                        <span class="text-4xl font-bold text-white">R{{ number_format($plan->price_monthly, 0) }}</span>
                        <span class="text-gray-400 text-sm">/month</span>
                    </div>

                    @if ($plan->price_annual)
                    <p class="mt-1 text-xs text-emerald-400">
                        R{{ number_format($plan->price_annual, 0) }}/mo billed annually · save {{ $plan->annualDiscountPercent() }}%
                    </p>
                    @endif

                    <a href="{{ route('register') }}"
                       class="mt-6 block text-center py-2.5 rounded-xl font-semibold text-sm transition
                              {{ $plan->is_featured
                                  ? 'bg-indigo-600 hover:bg-indigo-500 text-white'
                                  : 'bg-gray-800 hover:bg-gray-700 text-gray-200' }}">
                        Get started
                    </a>

                    <ul class="mt-6 space-y-2.5">
                        @foreach ($plan->planModules as $pm)
                        <li class="flex items-center gap-2.5 text-sm text-gray-300">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $pm->platformModule?->name ?? $pm->module_key }}
                        </li>
                        @endforeach
                    </ul>
                </div>

            </div>
            @endforeach
        </div>

        <p class="text-center text-gray-600 text-sm mt-8">
            Need only one module?
            <a href="{{ route('register') }}" class="text-indigo-400 hover:text-indigo-300">Sign up and activate individually from R99/mo →</a>
        </p>
        @endif
    </section>

    <!-- CTA -->
    <section class="border-t border-gray-800">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 py-20 text-center">

            <h2 class="text-3xl md:text-4xl font-bold">
                Build your system once.
                <span class="text-indigo-400">Run everything.</span>
            </h2>

            <p class="text-gray-400 mt-4">
                No complexity. No fragmented tools. One platform for your entire operation.
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