@php
    $assetBase = asset('site-templates/aroma-beauty-spa');
    $socials = $branding->socials ?? [];
    $heroImage = $branding->hero_image_url ?? $assetBase . '/images/banner/banner.jpg';
@endphp
<x-site-layout :tenant="$tenant" :branding="$branding" :template="$template">

    <div x-data="{ mobileNavOpen: false }" class="bg-[var(--site-bg)] text-[var(--site-text)]">

        {{-- Nav --}}
        <header class="fixed inset-x-0 top-0 z-40 border-b border-[var(--site-border)] bg-[var(--site-nav-bg)] backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="#home" class="flex items-center gap-2">
                    @if($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-9 w-auto object-contain">
                    @else
                        <span class="font-display text-lg font-bold text-[var(--site-text)]">{{ $tenant->name }}</span>
                    @endif
                </a>
                <nav class="hidden gap-7 text-sm font-medium text-[var(--site-text)] md:flex">
                    <a href="#home" class="transition hover:text-[var(--tenant-primary)]">Home</a>
                    <a href="#services" class="transition hover:text-[var(--tenant-primary)]">Services</a>
                    <a href="#about" class="transition hover:text-[var(--tenant-primary)]">About</a>
                    <a href="#our-team" class="transition hover:text-[var(--tenant-primary)]">Team</a>
                    <a href="#portfolio" class="transition hover:text-[var(--tenant-primary)]">Gallery</a>
                    <a href="#pricing" class="transition hover:text-[var(--tenant-primary)]">Pricing</a>
                    <a href="#contact-us" class="transition hover:text-[var(--tenant-primary)]">Contact</a>
                </nav>
                <button @click="mobileNavOpen = !mobileNavOpen" class="text-[var(--site-text)] md:hidden" aria-label="Toggle navigation">
                    <i class="fa" :class="mobileNavOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>
            </div>
            <nav x-cloak x-show="mobileNavOpen" x-transition class="border-t border-[var(--site-border)] bg-[var(--site-bg)] px-4 py-3 md:hidden">
                <a href="#home" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Home</a>
                <a href="#services" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Services</a>
                <a href="#about" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">About</a>
                <a href="#our-team" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Team</a>
                <a href="#portfolio" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Gallery</a>
                <a href="#pricing" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Pricing</a>
                <a href="#contact-us" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Contact</a>
            </nav>
        </header>

        {{-- Hero (photo background — dark overlay + white text by design) --}}
        <section id="home" class="relative flex min-h-[85vh] items-center justify-center overflow-hidden bg-cover bg-center pt-16 text-center"
                 style="background-image: linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.7)), url('{{ $heroImage }}')">
            <div class="px-4">
                <h1 class="font-display text-4xl font-extrabold text-white sm:text-6xl">Feel <span class="text-[var(--tenant-accent)]">Beautiful</span>, Every Day</h1>
                <p class="mx-auto mt-5 max-w-lg text-white/80">{{ $branding->description ?? 'A calm space to slow down and be looked after — book your next treatment today.' }}</p>
                <a href="#pricing" class="mt-8 inline-block rounded-lg bg-[var(--tenant-primary)] px-8 py-3 text-sm font-semibold text-white transition hover:brightness-90">View Packages</a>
            </div>
        </section>

        {{-- Services --}}
        <section id="services" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">What's Best For You</h2>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="item in [
                        { icon: 'fa-leaf', title: 'Aroma Therapy', text: 'Essential-oil treatments designed to ease tension and calm the mind.' },
                        { icon: 'fa-smile-o', title: 'Facials', text: 'Deep-cleansing facials tailored to your skin type, leaving you glowing.' },
                        { icon: 'fa-hand-paper-o', title: 'Manicure', text: 'Classic and gel manicures, finished exactly the way you like them.' },
                        { icon: 'fa-tint', title: 'Body Spa', text: 'Full-body treatments that combine massage, scrubs, and total relaxation.' },
                        { icon: 'fa-heartbeat', title: 'Head Massage', text: 'A tension-relieving head and scalp massage to unwind after a long week.' },
                        { icon: 'fa-magic', title: 'Hair Spa', text: 'Nourishing hair treatments that repair, hydrate, and restore shine.' },
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
                <img src="{{ $assetBase }}/images/about.png" alt="{{ $tenant->name }}" class="rounded-xl">
                <div>
                    <h3 class="font-display text-2xl font-bold text-[var(--site-text)]">Our Beauty Studio</h3>
                    <p class="mt-4 text-[var(--site-text-muted)]">{{ $branding->description ?? 'Tell your customers about your business — add a description in your website branding settings.' }}</p>
                    <ul class="mt-6 space-y-2">
                        <template x-for="label in ['Aroma Therapy', 'Manicure', 'Massage', 'Body Spa', 'Hair Spa']" :key="label">
                            <li class="flex items-center gap-2 text-[var(--site-text)]">
                                <i class="fa fa-angle-double-right text-[var(--tenant-accent)]"></i>
                                <span x-text="label"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </section>

        {{-- Team --}}
        <section id="our-team" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Our Team</h2>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="t in [
                        { img: '{{ $assetBase }}/images/team/01.jpg', name: 'Micky Deo', role: 'Founder' },
                        { img: '{{ $assetBase }}/images/team/02.jpg', name: 'Mike Timobbs', role: 'Sr. Stylist' },
                        { img: '{{ $assetBase }}/images/team/03.jpg', name: 'Remo Silvaus', role: 'Sr. Therapist' },
                        { img: '{{ $assetBase }}/images/team/04.jpg', name: 'Niscal Deon', role: 'Massage Therapist' },
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
                items: [
                    { img: '{{ $assetBase }}/images/portfolio/01.jpg', tags: ['aroma'] },
                    { img: '{{ $assetBase }}/images/portfolio/02.jpg', tags: ['manicure', 'spa'] },
                    { img: '{{ $assetBase }}/images/portfolio/03.jpg', tags: ['aroma'] },
                    { img: '{{ $assetBase }}/images/portfolio/04.jpg', tags: ['manicure'] },
                    { img: '{{ $assetBase }}/images/portfolio/05.jpg', tags: ['aroma', 'spa'] },
                    { img: '{{ $assetBase }}/images/portfolio/06.jpg', tags: ['manicure'] },
                    { img: '{{ $assetBase }}/images/portfolio/07.jpg', tags: ['aroma', 'spa'] },
                    { img: '{{ $assetBase }}/images/portfolio/08.jpg', tags: ['manicure'] },
                ],
            }"
        >
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Gallery</h2>

                <div class="mt-8 flex flex-wrap justify-center gap-2">
                    <template x-for="f in [
                        { key: '*', label: 'All' },
                        { key: 'aroma', label: 'Aroma' },
                        { key: 'manicure', label: 'Manicure' },
                        { key: 'spa', label: 'Body Spa' },
                    ]" :key="f.key">
                        <button
                            @click="filter = f.key"
                            class="rounded-full border px-4 py-1.5 text-sm transition"
                            :class="filter === f.key ? 'border-[var(--tenant-primary)] bg-[var(--tenant-primary)]/10 text-[var(--tenant-primary)]' : 'border-[var(--site-border-2)] text-[var(--site-text-muted)] hover:border-gray-400'"
                            x-text="f.label"
                        ></button>
                    </template>
                </div>

                <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    <template x-for="item in items" :key="item.img">
                        <button
                            x-show="filter === '*' || item.tags.includes(filter)"
                            @click="active = item.img; open = true"
                            class="group relative aspect-square overflow-hidden rounded-lg border border-[var(--site-border)]"
                        >
                            <img :src="item.img" alt="Studio work" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
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
                <img :src="active" alt="Studio work enlarged" class="max-h-[85vh] max-w-full rounded-lg object-contain">
            </div>
        </section>

        {{-- Pricing --}}
        <section id="pricing" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Pricing</h2>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="plan in [
                        { name: 'Essentials', price: 45, features: ['30-min facial', 'Express manicure', 'Head massage', 'Complimentary tea'], featured: false },
                        { name: 'Signature', price: 85, features: ['60-min facial', 'Gel manicure', 'Full body massage', 'Complimentary tea'], featured: true },
                        { name: 'Deluxe', price: 125, features: ['90-min facial', 'Gel manicure & pedicure', 'Aroma body spa', 'Complimentary tea & snacks'], featured: false },
                        { name: 'Ultimate', price: 185, features: ['Full spa day', 'Mani, pedi & hair spa', 'Deep tissue massage', 'Private lounge access'], featured: false },
                    ]" :key="plan.name">
                        <div
                            class="flex flex-col rounded-xl border p-6 text-left"
                            :class="plan.featured ? 'border-[var(--tenant-accent)] bg-[var(--tenant-accent)]/5' : 'border-[var(--site-border)] bg-[var(--site-surface)]'"
                        >
                            <div class="text-center">
                                <span class="text-3xl font-extrabold text-[var(--site-text)]" x-text="`$${plan.price}`"></span>
                                <span class="block text-xs uppercase tracking-wide text-[var(--site-text-faint)]">per visit</span>
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
                            >Book Now</a>
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
                    { label: 'Happy Clients', target: 3200, value: 0 },
                    { label: 'Therapists', target: 12, value: 0 },
                    { label: 'Treatments', target: 25, value: 0 },
                    { label: 'Years Open', target: 8, value: 0 },
                ],
            }"
            x-init="
                stats.forEach((s) => {
                    const step = Math.max(1, Math.ceil(s.target / 60));
                    const t = setInterval(() => {
                        s.value = Math.min(s.target, s.value + step);
                        if (s.value >= s.target) clearInterval(t);
                    }, 20);
                })
            "
        >
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">A Studio People Trust</h2>

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
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">What Our Clients Say</h2>

                <div class="mt-12 grid gap-6 md:grid-cols-3">
                    <template x-for="t in [
                        { img: '{{ $assetBase }}/images/pic1.jpg', name: 'Naledi P.', role: 'Regular Client', quote: 'The most relaxing hour of my week. My therapist always remembers exactly what pressure I like.' },
                        { img: '{{ $assetBase }}/images/pic2.jpg', name: 'Chantelle W.', role: 'Regular Client', quote: 'Booked a facial on a whim and now I\'m a monthly regular. My skin has never looked better.' },
                        { img: '{{ $assetBase }}/images/pic1.jpg', name: 'Farah A.', role: 'First-time Visitor', quote: 'Clean, calm, and the staff made me feel completely at ease from the moment I walked in.' },
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
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Book Your Visit</h2>
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
