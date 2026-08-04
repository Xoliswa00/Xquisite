<x-site-layout :tenant="$tenant" :branding="$branding" :template="$template">

    <div class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 px-4 py-16">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(0,120,212,0.15),_transparent_55%)]"></div>

        <div class="relative w-full max-w-2xl rounded-xl border border-slate-800 bg-slate-900 p-8 text-center shadow-2xl sm:p-12">
            <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--tenant-accent)]">Coming Soon</p>
            <h1 class="mt-3 text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $tenant->name }}</h1>
            <p class="mx-auto mt-4 max-w-md text-slate-400">
                {{ $branding->description ?? "We're under construction and putting the finishing touches on our new website. We'll be up and running soon." }}
            </p>

            {{-- Countdown --}}
            <div
                x-data="countdown('{{ now()->addDays(30)->toIso8601String() }}')"
                x-init="start()"
                class="mx-auto mt-10 grid max-w-md grid-cols-4 gap-3"
            >
                <template x-for="unit in units" :key="unit.label">
                    <div class="rounded-lg border border-slate-800 bg-slate-950 py-4">
                        <div class="text-2xl font-bold text-[var(--tenant-accent)] sm:text-3xl" x-text="unit.value"></div>
                        <div class="mt-1 text-[11px] uppercase tracking-wide text-slate-500" x-text="unit.label"></div>
                    </div>
                </template>
            </div>

            {{-- Newsletter signup --}}
            <div class="mx-auto mt-10 max-w-md" x-data="{ submitted: false, email: '' }">
                <form class="flex flex-col gap-3 sm:flex-row" @submit.prevent="submitted = true">
                    <label for="email" class="sr-only">Email address</label>
                    <input
                        id="email"
                        type="email"
                        required
                        x-model="email"
                        placeholder="Enter your email address"
                        class="w-full flex-1 rounded-lg border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/40"
                    >
                    <button
                        type="submit"
                        class="rounded-lg bg-[var(--tenant-primary)] px-6 py-3 text-sm font-semibold text-white transition hover:brightness-90 focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/40"
                    >
                        Notify Me
                    </button>
                </form>
                <p x-cloak x-show="submitted" class="mt-3 text-sm text-emerald-400">
                    Thanks — we'll email you the moment we launch.
                </p>
            </div>

            {{-- Social icons --}}
            @php $socials = $branding->socials ?? []; @endphp
            @if (array_filter($socials))
                <p class="mt-10 text-xs uppercase tracking-widest text-slate-500">Follow along</p>
                <ul class="mt-4 flex justify-center gap-4">
                    @foreach (['linkedin' => 'linkedin-in', 'facebook' => 'facebook-f', 'twitter' => 'x-twitter', 'youtube' => 'youtube'] as $platform => $icon)
                        @if (!empty($socials[$platform]))
                            <li>
                                <a href="{{ $socials[$platform] }}" target="_blank" rel="noopener"
                                   class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-800 text-slate-400 transition hover:border-[var(--tenant-primary)] hover:text-[var(--tenant-primary)]">
                                    <i class="fa-brands fa-{{ $icon }}"></i>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif

            <p class="mt-10 text-xs text-slate-600">&copy; {{ now()->year }} {{ $tenant->name }}. All rights reserved.</p>
        </div>
    </div>

    <script>
        function countdown(targetDate) {
            return {
                units: [
                    { label: 'Days', value: '00' },
                    { label: 'Hours', value: '00' },
                    { label: 'Minutes', value: '00' },
                    { label: 'Seconds', value: '00' },
                ],
                start() {
                    const target = new Date(targetDate).getTime();
                    const tick = () => {
                        const diff = Math.max(0, target - Date.now());
                        const pad = (n) => String(n).padStart(2, '0');
                        this.units = [
                            { label: 'Days', value: pad(Math.floor(diff / 86400000)) },
                            { label: 'Hours', value: pad(Math.floor((diff / 3600000) % 24)) },
                            { label: 'Minutes', value: pad(Math.floor((diff / 60000) % 60)) },
                            { label: 'Seconds', value: pad(Math.floor((diff / 1000) % 60)) },
                        ];
                    };
                    tick();
                    setInterval(tick, 1000);
                },
            };
        }
    </script>

</x-site-layout>
