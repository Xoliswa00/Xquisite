@php
    $assetBase = asset('site-templates/add-life-fitness');
    $socials = $branding->socials ?? [];
    $heroImage = $branding->hero_image_url ?? $assetBase . '/images/banner/banner.jpg';
    $aboutImage = $branding->about_image_url ?? $assetBase . '/images/about.png';
    $customGallery = !empty($branding->gallery_images) ? array_values($branding->gallery_images) : null;
@endphp
<x-site-layout :tenant="$tenant" :branding="$branding" :template="$template">

    <div x-data="{ mobileNavOpen: false }" class="bg-[var(--site-bg)] text-[var(--site-text)]">

        {{-- Nav --}}
        <header class="fixed inset-x-0 top-0 z-40 border-b border-[var(--site-border)] bg-[var(--site-nav-bg)] backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="#home" class="flex shrink-0 items-center gap-2">
                    @if($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-9 w-auto object-contain">
                    @else
                        <span class="font-display text-lg font-bold text-[var(--site-text)]">{{ $tenant->name }}</span>
                    @endif
                </a>
                <nav class="hidden gap-7 text-sm font-medium text-[var(--site-text)] md:flex">
                    <a href="#home" class="transition hover:text-[var(--tenant-primary)]">Home</a>
                    <a href="#services" class="transition hover:text-[var(--tenant-primary)]">Classes</a>
                    <a href="#about" class="transition hover:text-[var(--tenant-primary)]">About</a>
                    <a href="#our-team" class="transition hover:text-[var(--tenant-primary)]">Trainers</a>
                    <a href="#portfolio" class="transition hover:text-[var(--tenant-primary)]">Gallery</a>
                    <a href="#pricing" class="transition hover:text-[var(--tenant-primary)]">Pricing</a>
                    <a href="#contact-us" class="transition hover:text-[var(--tenant-primary)]">Contact</a>
                </nav>
                <button @click="mobileNavOpen = !mobileNavOpen" class="shrink-0 text-[var(--site-text)] md:hidden" aria-label="Toggle navigation">
                    <i class="fa" :class="mobileNavOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>
            </div>
            <nav x-cloak x-show="mobileNavOpen" x-transition class="border-t border-[var(--site-border)] bg-[var(--site-bg)] px-4 py-3 md:hidden">
                <a href="#home" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Home</a>
                <a href="#services" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Classes</a>
                <a href="#about" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">About</a>
                <a href="#our-team" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Trainers</a>
                <a href="#portfolio" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Gallery</a>
                <a href="#pricing" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Pricing</a>
                <a href="#contact-us" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Contact</a>
            </nav>
        </header>

        {{-- Hero (photo background — dark overlay + white text by design) --}}
        <section id="home" class="relative flex min-h-[85vh] items-center justify-center overflow-hidden bg-cover bg-center pt-16 text-center"
                 style="background-image: linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.7)), url('{{ $heroImage }}')">
            <div class="px-4">
                <h1 class="font-display text-4xl font-extrabold text-white sm:text-6xl">Stronger than <span class="text-[var(--tenant-accent)]">EVER</span></h1>
                <p class="mx-auto mt-5 max-w-lg text-white/80">{{ $branding->description ?? 'Join us and start your fitness journey today.' }}</p>
                <a href="#pricing" class="mt-8 inline-block rounded-lg bg-[var(--tenant-primary)] px-8 py-3 text-sm font-semibold text-white transition hover:brightness-90">Start Now</a>
            </div>
        </section>

        {{-- Services --}}
        <section id="services" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">What's Best For You</h2>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="item in [
                        { icon: 'fa-futbol-o', title: 'Aerobic', text: 'High-energy group classes built to get your heart rate up and keep you coming back.' },
                        { icon: 'fa-compass', title: 'Cardio', text: 'Structured cardio programs for every fitness level, from first-timers to regulars.' },
                        { icon: 'fa-database', title: 'Strength Training', text: 'Build real strength with guided weight training and progressive programs.' },
                        { icon: 'fa-bar-chart', title: 'Group Classes', text: 'Train alongside others in a class that keeps you motivated and accountable.' },
                        { icon: 'fa-paper-plane-o', title: 'Personal Training', text: 'One-on-one sessions tailored to your goals, with a trainer who knows your progress.' },
                        { icon: 'fa-bullseye', title: 'Nutrition Coaching', text: 'Guidance on eating right to match your training — because results start in the kitchen too.' },
                    ]" :key="item.title">
                        <div class="rounded-xl border border-[var(--site-border)] bg-[var(--site-surface)] p-6 text-left transition hover:border-[var(--tenant-primary)]/50 hover:shadow-sm">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--tenant-primary)]/10 text-xl text-[var(--tenant-primary)]">
                                <i class="fa" :class="item.icon"></i>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-[var(--site-text)]" x-text="item.title"></h3>
                            <p class="mt-2 text-sm text-[var(--site-text-muted)]" x-text="item.text"></p>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- About --}}
        <section id="about" class="scroll-mt-16 bg-[var(--site-surface)] py-20">
            <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 md:grid-cols-2 md:items-center">
                <img src="{{ $aboutImage }}" alt="{{ $tenant->name }}" class="rounded-xl">
                <div>
                    <h3 class="font-display text-2xl font-bold text-[var(--site-text)]">Our Fitness Studio</h3>
                    <p class="mt-4 text-[var(--site-text-muted)]">{{ $branding->description ?? 'Tell your customers about your business — add a description in your website branding settings.' }}</p>
                    <ul class="mt-6 space-y-2">
                        <template x-for="label in ['Aerobic', 'Cardio', 'Abdomen', 'Special Trainer', 'Round the clock']" :key="label">
                            <li class="flex items-center gap-2 text-[var(--site-text)]">
                                <i class="fa fa-angle-double-right text-[var(--tenant-accent)]"></i>
                                <span x-text="label"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </section>

        {{-- Trainers --}}
        <section id="our-team" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Our Trainers</h2>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="t in [
                        { img: '{{ $assetBase }}/images/team/01.jpg', name: 'Micky Deo', role: 'Founder' },
                        { img: '{{ $assetBase }}/images/team/02.jpg', name: 'Mike Timobbs', role: 'Sr. Trainer' },
                        { img: '{{ $assetBase }}/images/team/03.jpg', name: 'Remo Silvaus', role: 'Sr. Trainer' },
                        { img: '{{ $assetBase }}/images/team/04.jpg', name: 'Niscal Deon', role: 'Jr. Trainer' },
                    ]" :key="t.name">
                        <div class="overflow-hidden rounded-xl border border-[var(--site-border)] bg-[var(--site-surface)]">
                            <img :src="t.img" :alt="t.name" class="h-56 w-full object-cover">
                            <div class="p-4">
                                <h3 class="font-semibold text-[var(--site-text)]" x-text="t.name"></h3>
                                <span class="text-sm text-[var(--site-text-faint)]" x-text="t.role"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- Gallery / Portfolio --}}
        <section
            id="portfolio"
            class="scroll-mt-16 bg-[var(--site-surface)] py-20"
            x-data="{
                filter: '*',
                open: false,
                active: null,
                items: {{ $customGallery ? \Illuminate\Support\Js::from(collect($customGallery)->map(fn ($img) => ['img' => $img, 'tags' => ['custom']])->all()) : \Illuminate\Support\Js::from([
                    ['img' => $assetBase . '/images/portfolio/01.jpg', 'tags' => ['designing']],
                    ['img' => $assetBase . '/images/portfolio/02.jpg', 'tags' => ['mobile', 'development']],
                    ['img' => $assetBase . '/images/portfolio/03.jpg', 'tags' => ['designing']],
                    ['img' => $assetBase . '/images/portfolio/04.jpg', 'tags' => ['mobile']],
                    ['img' => $assetBase . '/images/portfolio/05.jpg', 'tags' => ['designing', 'development']],
                    ['img' => $assetBase . '/images/portfolio/06.jpg', 'tags' => ['mobile']],
                    ['img' => $assetBase . '/images/portfolio/07.jpg', 'tags' => ['designing', 'development']],
                    ['img' => $assetBase . '/images/portfolio/08.jpg', 'tags' => ['mobile']],
                ]) }},
            }"
        >
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Gallery</h2>

                @unless($customGallery)
                <div class="mt-8 flex flex-wrap justify-center gap-2">
                    <template x-for="f in [
                        { key: '*', label: 'All Works' },
                        { key: 'designing', label: 'Designing' },
                        { key: 'mobile', label: 'Mobile App' },
                        { key: 'development', label: 'Development' },
                    ]" :key="f.key">
                        <button
                            @click="filter = f.key"
                            class="rounded-full border px-4 py-1.5 text-sm transition"
                            :class="filter === f.key ? 'border-[var(--tenant-primary)] bg-[var(--tenant-primary)]/10 text-[var(--tenant-primary)]' : 'border-[var(--site-border-2)] text-[var(--site-text-muted)] hover:border-gray-400'"
                            x-text="f.label"
                        ></button>
                    </template>
                </div>
                @endunless

                <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    <template x-for="item in items" :key="item.img">
                        <button
                            x-show="filter === '*' || item.tags.includes(filter)"
                            @click="active = item.img; open = true"
                            class="group relative aspect-square overflow-hidden rounded-lg border border-[var(--site-border)]"
                        >
                            <img :src="item.img" alt="Portfolio work" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                            <span class="absolute inset-0 flex items-center justify-center bg-black/0 text-white opacity-0 transition group-hover:bg-black/50 group-hover:opacity-100">
                                <i class="fa fa-eye"></i>
                            </span>
                        </button>
                    </template>
                </div>
            </div>

            <div
                x-cloak x-show="open" x-transition.opacity
                @keydown.escape.window="open = false"
                @click.self="open = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
            >
                <button @click="open = false" class="absolute right-6 top-6 text-2xl text-white/80 hover:text-white" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
                <img :src="active" alt="Portfolio work enlarged" class="max-h-[85vh] max-w-full rounded-lg object-contain">
            </div>
        </section>

        {{-- Pricing --}}
        <section id="pricing" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Pricing</h2>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="plan in [
                        { name: 'Basic', price: 45, features: ['1 Domain', '100GB Disk Space', 'Unlimited Bandwidth', 'Shared SSL Certificate', '10 Email Address', '24/7 Support'], featured: false },
                        { name: 'Bronze', price: 85, features: ['5 Domain', '500GB Disk Space', 'Unlimited Bandwidth', 'Shared SSL Certificate', '30 Email Address', '24/7 Support'], featured: true },
                        { name: 'Silver', price: 125, features: ['10 Domain', '2GB Disk Space', 'Unlimited Bandwidth', 'Shared SSL Certificate', '50 Email Address', '24/7 Support'], featured: false },
                        { name: 'Gold', price: 185, features: ['15 Domain', '10GB Disk Space', 'Unlimited Bandwidth', 'Shared SSL Certificate', '100 Email Address', '24/7 Support'], featured: false },
                    ]" :key="plan.name">
                        <div
                            class="flex flex-col rounded-xl border p-6 text-left"
                            :class="plan.featured ? 'border-[var(--tenant-accent)] bg-[var(--tenant-accent)]/5' : 'border-[var(--site-border)] bg-[var(--site-surface)]'"
                        >
                            <div class="text-center">
                                <span class="text-3xl font-extrabold text-[var(--site-text)]" x-text="`$${plan.price}`"></span>
                                <span class="block text-xs uppercase tracking-wide text-[var(--site-text-faint)]">per month</span>
                                <span class="mt-2 block font-display text-lg font-semibold" :class="plan.featured ? 'text-[var(--tenant-accent)]' : 'text-[var(--site-text)]'" x-text="plan.name"></span>
                            </div>
                            <ul class="mt-6 flex-1 space-y-2 text-sm text-[var(--site-text-muted)]">
                                <template x-for="f in plan.features" :key="f">
                                    <li class="border-t border-[var(--site-border)] pt-2 first:border-0 first:pt-0" x-text="f"></li>
                                </template>
                            </ul>
                            <a
                                href="#contact-us"
                                class="mt-6 block rounded-lg px-4 py-2.5 text-center text-sm font-semibold transition"
                                :class="plan.featured ? 'bg-[var(--tenant-accent)] text-[var(--site-text)] hover:brightness-90' : 'bg-[var(--tenant-primary)] text-white hover:brightness-90'"
                            >Get It Now!</a>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- Stats --}}
        <section
            id="business-stats"
            class="scroll-mt-16 bg-[var(--site-surface)] py-20"
            x-data="{
                stats: [
                    { label: 'Clients', target: 6850, value: 0 },
                    { label: 'Trainers', target: 1465, value: 0 },
                    { label: 'Programs', target: 4325, value: 0 },
                    { label: 'Successes', target: 2568, value: 0 },
                ],
            }"
            x-init="
                stats.forEach((s) => {
                    const step = Math.ceil(s.target / 60);
                    const t = setInterval(() => {
                        s.value = Math.min(s.target, s.value + step);
                        if (s.value >= s.target) clearInterval(t);
                    }, 20);
                })
            "
        >
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Healthy Report</h2>

                <div class="mt-12 grid grid-cols-2 gap-6 lg:grid-cols-4">
                    <template x-for="s in stats" :key="s.label">
                        <div>
                            <div class="text-4xl font-extrabold text-[var(--tenant-primary)]" x-text="s.value.toLocaleString()"></div>
                            <strong class="mt-2 block text-sm uppercase tracking-wide text-[var(--site-text-muted)]" x-text="s.label"></strong>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- Testimonials --}}
        <section id="testimonial" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Testimonial</h2>

                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    <template x-for="t in [
                        { img: '{{ $assetBase }}/images/pic1.jpg', name: 'Thabo M.', role: 'Member since 2024', quote: 'Six months in and I\'ve never felt stronger. The trainers actually pay attention to your form, not just the clock.' },
                        { img: '{{ $assetBase }}/images/pic2.jpg', name: 'Sarah L.', role: 'Member since 2023', quote: 'The group classes keep me showing up even on the days I don\'t feel like it. Best decision I\'ve made this year.' },
                        { img: '{{ $assetBase }}/images/pic1.jpg', name: 'David R.', role: 'Member since 2024', quote: 'Great equipment, cleaner than any gym I\'ve been to, and the staff genuinely want to see you improve.' },
                    ]" :key="t.name + t.quote">
                        <div class="rounded-xl border border-[var(--site-border)] bg-[var(--site-surface)] p-6 text-left">
                            <blockquote class="text-sm italic text-[var(--site-text-muted)]" x-text="t.quote"></blockquote>
                            <div class="mt-5 flex items-center gap-3">
                                <img :src="t.img" alt="Client" class="h-12 w-12 rounded-full object-cover">
                                <div>
                                    <h5 class="text-sm font-semibold text-[var(--site-text)]" x-text="t.name"></h5>
                                    <h6 class="text-xs text-[var(--site-text-faint)]" x-text="t.role"></h6>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- Contact --}}
        <section id="contact-us" class="scroll-mt-16 bg-[var(--site-surface)] pt-20 text-center">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Contact Us</h2>
            </div>
        </section>

        <section id="contact" class="bg-[var(--site-surface)] pb-20">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 md:grid-cols-3">
                <div class="rounded-xl border border-[var(--site-border)] bg-[var(--site-bg)] p-6">
                    <h3 class="font-semibold text-[var(--site-text)]">Contact Info</h3>
                    <address class="mt-4 not-italic text-sm leading-relaxed text-[var(--site-text-muted)]">
                        <strong class="text-[var(--site-text)]">{{ $tenant->name }}</strong><br>
                        @if($tenant->address) {{ $tenant->address }}<br> @endif
                        @if($branding->display_phone) <abbr title="Phone">P:</abbr> {{ $branding->display_phone }}<br> @endif
                        @if($branding->display_email) {{ $branding->display_email }} @endif
                    </address>
                    @if($branding->whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $branding->whatsapp_number) }}" target="_blank" rel="noopener"
                           class="mt-4 inline-flex items-center gap-2 text-sm text-emerald-600 hover:text-emerald-700">
                            <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    @endif
                </div>
                <div class="md:col-span-2" x-data="{ sent: false }">
                    <p x-cloak x-show="sent" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        Thanks — your message has been sent.
                    </p>
                    <form @submit.prevent="sent = true" class="grid gap-4 sm:grid-cols-2">
                        <input type="text" name="name" required placeholder="Name" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30">
                        <input type="email" name="email" required placeholder="Email" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30">
                        <input type="text" name="subject" required placeholder="Subject" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30 sm:col-span-2">
                        <textarea name="message" required rows="6" placeholder="Message" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30 sm:col-span-2"></textarea>
                        <button type="submit" class="rounded-lg bg-[var(--tenant-primary)] px-6 py-3 text-sm font-semibold text-white transition hover:brightness-90 sm:col-span-2">Send Message</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer id="footer" class="border-t border-[var(--site-border)] bg-[var(--site-bg)] py-8">
            <div class="mx-auto flex max-w-6xl flex-col items-center gap-4 px-4 text-center sm:flex-row sm:justify-between sm:px-6 sm:text-left">
                <p class="text-xs text-[var(--site-text-faint)]">&copy; {{ now()->year }} {{ $tenant->name }}. All rights reserved.</p>
                @if (array_filter($socials))
                    <ul class="flex gap-4 text-[var(--site-text-faint)]">
                        @foreach (['facebook' => 'fa-facebook', 'twitter' => 'fa-twitter', 'linkedin' => 'fa-linkedin', 'instagram' => 'fa-instagram', 'youtube' => 'fa-youtube'] as $platform => $icon)
                            @if (!empty($socials[$platform]))
                                <li><a href="{{ $socials[$platform] }}" target="_blank" rel="noopener" class="hover:text-[var(--tenant-primary)]"><i class="fa {{ $icon }}"></i></a></li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
        </footer>

    </div>

</x-site-layout>
