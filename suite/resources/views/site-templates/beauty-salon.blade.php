@php
    $assetBase = asset('site-templates/beauty-salon');
    $socials = $branding->socials ?? [];
    $aboutImage = $branding->about_image_url ?? $assetBase . '/images/about.png';
    $customGallery = !empty($branding->gallery_images) ? array_values($branding->gallery_images) : null;
@endphp
<x-site-layout :tenant="$tenant" :branding="$branding" :template="$template">

    <div x-data="{ mobileNavOpen: false }" class="bg-[var(--site-bg)] text-[var(--site-text)]">

        {{-- Nav --}}
        <header class="sticky top-0 z-40 border-b border-[var(--site-border)] bg-[var(--site-nav-bg)] backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="#header" class="flex shrink-0 items-center gap-2">
                    @if($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-9 w-auto object-contain">
                    @else
                        <span class="font-display text-lg font-bold text-[var(--site-text)]">{{ $tenant->name }}</span>
                    @endif
                </a>
                <nav class="hidden gap-8 text-sm font-medium text-[var(--site-text)] md:flex">
                    <a href="#header" class="transition hover:text-[var(--tenant-primary)]">Home</a>
                    <a href="#about" class="transition hover:text-[var(--tenant-primary)]">About</a>
                    <a href="#service" class="transition hover:text-[var(--tenant-primary)]">Services</a>
                    <a href="#gallery" class="transition hover:text-[var(--tenant-primary)]">Gallery</a>
                    <a href="#feedback" class="transition hover:text-[var(--tenant-primary)]">Reviews</a>
                    <a href="#contact" class="transition hover:text-[var(--tenant-primary)]">Contact</a>
                </nav>
                <button @click="mobileNavOpen = !mobileNavOpen" class="shrink-0 text-[var(--site-text)] md:hidden" aria-label="Toggle navigation">
                    <i class="fa" :class="mobileNavOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>
            </div>
            <nav x-cloak x-show="mobileNavOpen" x-transition class="border-t border-[var(--site-border)] bg-[var(--site-bg)] px-4 py-3 md:hidden">
                <a href="#header" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Home</a>
                <a href="#about" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">About</a>
                <a href="#service" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Services</a>
                <a href="#gallery" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Gallery</a>
                <a href="#feedback" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Reviews</a>
                <a href="#contact" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Contact</a>
            </nav>
        </header>

        {{-- Hero carousel (photo background — dark overlay + white text by design) --}}
        <section
            id="header"
            class="relative flex h-[80vh] min-h-[520px] items-center justify-center overflow-hidden scroll-mt-16"
            x-data="{ slide: 0, slides: [
                    { img: '{{ $branding->hero_image_url ?? $assetBase . '/images/banner/banner.jpg' }}', title: 'Hair, Reinvented', sub: 'Cut, colour, and style — crafted around what actually suits you' },
                    { img: '{{ $assetBase }}/images/portfolio/02.jpg', title: 'Colour That Turns Heads', sub: 'From subtle balayage to bold transformations' },
                    { img: '{{ $assetBase }}/images/portfolio/05.jpg', title: 'Styled For Every Occasion', sub: 'Bridal, events, or just a Tuesday — we\'ve got you' },
                ] }"
            x-init="setInterval(() => slide = (slide + 1) % slides.length, 5000)"
        >
            <template x-for="(s, i) in slides" :key="i">
                <div
                    x-show="slide === i"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    class="absolute inset-0 bg-cover bg-center"
                    :style="`background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.55)), url(${s.img})`"
                >
                    <div class="flex h-full flex-col items-center justify-center px-4 text-center">
                        <h1 class="font-display text-4xl font-bold text-white sm:text-5xl" x-text="s.title"></h1>
                        <p class="mt-4 max-w-md text-white/80" x-text="s.sub"></p>
                        <a href="#contact" class="mt-8 rounded-lg bg-[var(--tenant-primary)] px-8 py-3 text-sm font-semibold text-white transition hover:brightness-90">Book an Appointment</a>
                    </div>
                </div>
            </template>

            <div class="absolute bottom-6 left-1/2 flex -translate-x-1/2 gap-2">
                <template x-for="(s, i) in slides" :key="i">
                    <button @click="slide = i" class="h-2.5 w-2.5 rounded-full transition" :class="slide === i ? 'bg-[var(--tenant-accent)]' : 'bg-white/40'"></button>
                </template>
            </div>
        </section>

        {{-- About --}}
        <section id="about" class="scroll-mt-16 bg-[var(--site-surface)] py-20">
            <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 md:grid-cols-2 md:items-center">
                <img src="{{ $aboutImage }}" alt="{{ $tenant->name }}" class="rounded-xl">
                <div>
                    <h3 class="font-display text-2xl font-bold text-[var(--site-text)]">Trending Styles, Trusted Hands</h3>
                    <p class="mt-4 text-[var(--site-text-muted)]">{{ $branding->description ?? 'Tell your customers about your business — add a description in your website branding settings.' }}</p>
                    <ul class="mt-6 space-y-2">
                        <template x-for="label in ['Precision cutting', 'Colour & balayage', 'Keratin treatments', 'Bridal styling', 'Consultations included']" :key="label">
                            <li class="flex items-center gap-2 text-[var(--site-text)]">
                                <i class="fa fa-angle-double-right text-[var(--tenant-accent)]"></i>
                                <span x-text="label"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </section>

        {{-- Services --}}
        <section id="service" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Our Services</h2>
                <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">
                    Every visit starts with a consultation, so what you get is always what you actually wanted.
                </p>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="item in [
                        { icon: 'fa-cut', title: 'Cuts & Styling', text: 'Precision cuts and blowouts tailored to your face shape and hair type.' },
                        { icon: 'fa-tint', title: 'Colour & Balayage', text: 'From your first colour to a full transformation, done gently and evenly.' },
                        { icon: 'fa-magic', title: 'Treatments', text: 'Keratin smoothing, deep conditioning, and repair treatments that last.' },
                        { icon: 'fa-heart', title: 'Bridal & Events', text: 'Trial run included — so your big day looks exactly how you pictured it.' },
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

        {{-- Team --}}
        <section id="our-team" class="scroll-mt-16 bg-[var(--site-surface)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Meet the Team</h2>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="t in [
                        { img: '{{ $assetBase }}/images/team/01.jpg', name: 'Lerato M.', role: 'Senior Stylist' },
                        { img: '{{ $assetBase }}/images/team/02.jpg', name: 'Thandiwe K.', role: 'Colour Specialist' },
                        { img: '{{ $assetBase }}/images/team/03.jpg', name: 'Amara N.', role: 'Stylist' },
                        { img: '{{ $assetBase }}/images/team/04.jpg', name: 'Zanele R.', role: 'Salon Manager' },
                    ]" :key="t.name">
                        <div class="overflow-hidden rounded-xl border border-[var(--site-border)] bg-[var(--site-bg)]">
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
            id="gallery"
            class="scroll-mt-16 bg-[var(--site-bg)] py-20"
            x-data="{
                filter: '*',
                open: false,
                active: null,
                items: {{ $customGallery ? \Illuminate\Support\Js::from(collect($customGallery)->map(fn ($img) => ['img' => $img, 'tags' => ['custom']])->all()) : \Illuminate\Support\Js::from([
                    ['img' => $assetBase . '/images/portfolio/01.jpg', 'tags' => ['cuts']],
                    ['img' => $assetBase . '/images/portfolio/02.jpg', 'tags' => ['colour']],
                    ['img' => $assetBase . '/images/portfolio/03.jpg', 'tags' => ['cuts', 'styling']],
                    ['img' => $assetBase . '/images/portfolio/04.jpg', 'tags' => ['bridal']],
                    ['img' => $assetBase . '/images/portfolio/05.jpg', 'tags' => ['styling', 'bridal']],
                    ['img' => $assetBase . '/images/portfolio/06.jpg', 'tags' => ['colour']],
                    ['img' => $assetBase . '/images/portfolio/07.jpg', 'tags' => ['cuts']],
                    ['img' => $assetBase . '/images/portfolio/08.jpg', 'tags' => ['styling']],
                ]) }},
            }"
        >
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Gallery</h2>

                @unless($customGallery)
                <div class="mt-8 flex flex-wrap justify-center gap-2">
                    <template x-for="f in [
                        { key: '*', label: 'All' },
                        { key: 'cuts', label: 'Cuts' },
                        { key: 'colour', label: 'Colour' },
                        { key: 'styling', label: 'Styling' },
                        { key: 'bridal', label: 'Bridal' },
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
                            <img :src="item.img" alt="Salon work" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
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
                <img :src="active" alt="Salon work enlarged" class="max-h-[85vh] max-w-full rounded-lg object-contain">
            </div>
        </section>

        {{-- Feedback --}}
        <section id="feedback" class="scroll-mt-16 bg-[var(--site-surface)] py-20">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">What Clients Say</h2>
                <div
                    class="mt-12"
                    x-data="{ i: 0, quotes: [
                        { text: 'First time I\'ve walked out of a salon and actually loved my colour straight away. No more guessing games.', name: 'Palesa D.', initials: 'PD' },
                        { text: 'Booked for my wedding morning and they made the whole team look incredible, on time, no stress.', name: 'Michelle A.', initials: 'MA' },
                        { text: 'My curls have never been healthier. They actually listen before they cut.', name: 'Refilwe S.', initials: 'RS' },
                    ] }"
                >
                    <template x-for="(q, idx) in quotes" :key="idx">
                        <blockquote x-show="i === idx" x-transition.opacity class="flex flex-col items-center gap-4">
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-[var(--tenant-primary)]/10 text-lg font-semibold text-[var(--tenant-primary)]" x-text="q.initials"></span>
                            <p class="max-w-xl text-lg italic text-[var(--site-text)]" x-text="q.text"></p>
                            <cite class="not-italic text-sm text-[var(--site-text-faint)]" x-text="q.name"></cite>
                        </blockquote>
                    </template>
                    <div class="mt-8 flex justify-center gap-2">
                        <template x-for="(q, idx) in quotes" :key="idx">
                            <button @click="i = idx" class="h-2.5 w-2.5 rounded-full transition" :class="i === idx ? 'bg-[var(--tenant-accent)]' : 'bg-gray-300'"></button>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        {{-- Contact --}}
        <section id="contact" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-xl px-4 sm:px-6">
                <div class="text-center">
                    <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Book Your Visit</h2>
                    @if($branding->display_email || $branding->display_phone || $tenant->address)
                        <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">
                            {{ $branding->display_email }}
                            @if($branding->display_email && $branding->display_phone) &middot; @endif
                            {{ $branding->display_phone }}
                            @if($tenant->address) <br>{{ $tenant->address }} @endif
                        </p>
                    @endif
                    @if($branding->whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $branding->whatsapp_number) }}" target="_blank" rel="noopener"
                           class="mt-4 inline-flex items-center gap-2 text-sm text-emerald-600 hover:text-emerald-700">
                            <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    @endif
                </div>

                <div x-data="{ sent: false }" class="mt-10">
                    <p x-cloak x-show="sent" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        Your message has been sent. Thank you!
                    </p>
                    <form @submit.prevent="sent = true" class="space-y-4">
                        <input name="name" type="text" required placeholder="Your Name *" class="w-full rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30">
                        <input name="email" type="email" required placeholder="E-mail address *" class="w-full rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30">
                        <textarea name="comment" required placeholder="Message *" rows="5" class="w-full rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30"></textarea>
                        <button type="submit" class="w-full rounded-lg bg-[var(--tenant-primary)] px-6 py-3 text-sm font-semibold text-white transition hover:brightness-90">Send Message</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="border-t border-[var(--site-border)] bg-[var(--site-bg)] py-10">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <a href="#header" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-[var(--site-border)] text-[var(--site-text-faint)] transition hover:border-[var(--tenant-primary)] hover:text-[var(--tenant-primary)]" aria-label="Back to top">
                    <i class="fa fa-angle-double-up"></i>
                </a>
                @if (array_filter($socials))
                    <ul class="mt-6 flex justify-center gap-4">
                        @foreach (['facebook' => 'fa-facebook', 'instagram' => 'fa-instagram', 'twitter' => 'fa-twitter', 'tiktok' => 'fa-tiktok'] as $platform => $icon)
                            @if (!empty($socials[$platform]))
                                <li><a href="{{ $socials[$platform] }}" target="_blank" rel="noopener" class="flex h-10 w-10 items-center justify-center rounded-full border border-[var(--site-border)] text-[var(--site-text-faint)] transition hover:border-[var(--tenant-primary)] hover:text-[var(--tenant-primary)]"><i class="fa {{ $icon }}"></i></a></li>
                            @endif
                        @endforeach
                    </ul>
                @endif
                <p class="mt-6 text-xs text-[var(--site-text-faint)]">&copy; {{ now()->year }} {{ $tenant->name }}. All rights reserved.</p>
            </div>
        </footer>

    </div>

</x-site-layout>
