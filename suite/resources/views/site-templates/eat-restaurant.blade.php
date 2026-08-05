@php
    $assetBase = asset('site-templates/eat-restaurant');
    $socials = $branding->socials ?? [];
    $aboutImage = $branding->about_image_url ?? $assetBase . '/img/gallery/img1.jpg';
    $galleryImages = !empty($branding->gallery_images) ? array_values($branding->gallery_images) : collect(range(1, 8))->map(fn ($n) => "{$assetBase}/img/gallery/img{$n}.jpg")->all();
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
                    <a href="#service" class="transition hover:text-[var(--tenant-primary)]">Our Services</a>
                    <a href="#aboutUs" class="transition hover:text-[var(--tenant-primary)]">About Us</a>
                    <a href="#gallery" class="transition hover:text-[var(--tenant-primary)]">Gallery</a>
                    <a href="#feedback" class="transition hover:text-[var(--tenant-primary)]">Feedback</a>
                    <a href="#contact" class="transition hover:text-[var(--tenant-primary)]">Contact</a>
                </nav>
                <button @click="mobileNavOpen = !mobileNavOpen" class="shrink-0 text-[var(--site-text)] md:hidden" aria-label="Toggle navigation">
                    <i class="fa" :class="mobileNavOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>
            </div>
            <nav x-cloak x-show="mobileNavOpen" x-transition class="border-t border-[var(--site-border)] bg-[var(--site-bg)] px-4 py-3 md:hidden">
                <a href="#header" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Home</a>
                <a href="#service" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Our Services</a>
                <a href="#aboutUs" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">About Us</a>
                <a href="#gallery" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Gallery</a>
                <a href="#feedback" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Feedback</a>
                <a href="#contact" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Contact</a>
            </nav>
        </header>

        {{-- Hero carousel (photo background — dark overlay + white text by design) --}}
        <section
            id="header"
            class="relative flex h-[80vh] min-h-[520px] items-center justify-center overflow-hidden scroll-mt-16"
            x-data="{ slide: 0, slides: [
                    { img: '{{ $branding->hero_image_url ?? $assetBase . '/img/slide1.jpg' }}', title: 'Good food, good company', sub: 'Fresh dishes made daily, served with a smile' },
                    { img: '{{ $assetBase }}/img/slide2.jpg', title: 'Made from scratch', sub: 'Real ingredients, honest cooking, no shortcuts' },
                    { img: '{{ $assetBase }}/img/slide3.jpg', title: 'A table always waiting', sub: 'Book ahead or walk in — we\'ll find you a seat' },
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
                    :style="`background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), url(${s.img})`"
                >
                    <div class="flex h-full flex-col items-center justify-center px-4 text-center">
                        <h1 class="font-display text-4xl font-bold text-white sm:text-5xl" x-text="s.title"></h1>
                        <p class="mt-4 max-w-md text-white/80" x-text="s.sub"></p>
                        <a href="#service" class="mt-8 rounded-lg bg-[var(--tenant-primary)] px-8 py-3 text-sm font-semibold text-white transition hover:brightness-90">Eat & Chat</a>
                    </div>
                </div>
            </template>

            <div class="absolute bottom-6 left-1/2 flex -translate-x-1/2 gap-2">
                <template x-for="(s, i) in slides" :key="i">
                    <button @click="slide = i" class="h-2.5 w-2.5 rounded-full transition" :class="slide === i ? 'bg-[var(--tenant-accent)]' : 'bg-white/40'"></button>
                </template>
            </div>
        </section>

        {{-- Services --}}
        <section id="service" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Eat & Chat</h2>
                <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">
                    Fresh ingredients, honest cooking, and a warm room to enjoy it in — every single day.
                </p>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="item in [
                        { icon: 'fa-heart', title: 'Local Favourites', text: 'The dishes our regulars order again and again — comfort food done right, every time.' },
                        { icon: 'fa-users', title: 'Group Dining', text: 'Big table, big appetite? We cater for groups and gatherings with the same care as a table for two.' },
                        { icon: 'fa-fire', title: 'Daily Specials', text: 'A new dish every day, built around whatever\'s freshest that morning.' },
                        { icon: 'fa-film', title: 'Desserts', text: 'House-made sweets to finish the meal — ask your server what\'s fresh out of the kitchen.' },
                        { icon: 'fa-cubes', title: 'Custom Cakes', text: 'Order ahead for birthdays, celebrations, or just because — made to order.' },
                        { icon: 'fa-envelope', title: 'Private Bookings', text: 'Planning something bigger? Get in touch and we\'ll help you plan the evening.' },
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
        <section id="aboutUs" class="scroll-mt-16 bg-[var(--site-surface)] py-20">
            <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 md:grid-cols-2 md:items-center">
                <img src="{{ $aboutImage }}" alt="{{ $tenant->name }}" class="rounded-xl">
                <div class="text-left">
                    <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Who We Are</h2>
                    <div class="mt-6 space-y-4 text-[var(--site-text-muted)]">
                        <p>{{ $branding->description ?? 'Tell your customers about your business — add a description in your website branding settings.' }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Gallery --}}
        <section id="gallery" class="scroll-mt-16 bg-[var(--site-bg)] py-20" x-data="{ open: false, active: null, images: {{ \Illuminate\Support\Js::from($galleryImages) }} }">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="text-center">
                    <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Our Gallery</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">A few plates, a few nights, a few reasons to come back.</p>
                </div>

                <div class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    <template x-for="(src, i) in images" :key="src">
                        <button @click="active = src; open = true" class="group relative aspect-square overflow-hidden rounded-lg border border-[var(--site-border)]">
                            <img :src="src" alt="Gallery photo" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                            <span class="absolute inset-0 flex items-center justify-center bg-black/0 text-white opacity-0 transition group-hover:bg-black/50 group-hover:opacity-100">
                                <i class="fa fa-search-plus"></i>
                            </span>
                        </button>
                    </template>
                </div>
            </div>

            <div
                x-cloak x-show="open" x-transition.opacity
                @keydown.escape.window="open = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
                @click.self="open = false"
            >
                <button @click="open = false" class="absolute right-6 top-6 text-2xl text-white/80 hover:text-white" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
                <img :src="active" alt="Gallery photo enlarged" class="max-h-[85vh] max-w-full rounded-lg object-contain">
            </div>
        </section>

        {{-- Feedback --}}
        <section id="feedback" class="scroll-mt-16 bg-[var(--site-surface)] py-20">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">What Our Clients Say</h2>
                <div
                    class="mt-12"
                    x-data="{ i: 0, quotes: [
                        { text: 'Best meal I\'ve had in months. The staff remembered my order from last time — that\'s how you know a place is good.', name: 'Priya N.', initials: 'PN' },
                        { text: 'We booked a table for eight with no notice and they still made it feel special. Will be back.', name: 'Marcus T.', initials: 'MT' },
                        { text: 'Everything tastes homemade. Honestly one of the few places where the specials board is worth trusting.', name: 'Aaliyah K.', initials: 'AK' },
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
                    <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Contact Us</h2>
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
                        @foreach (['twitter' => 'fa-twitter', 'facebook' => 'fa-facebook', 'linkedin' => 'fa-linkedin', 'instagram' => 'fa-instagram'] as $platform => $icon)
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
