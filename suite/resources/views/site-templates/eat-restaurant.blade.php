@php
    $assetBase = asset('site-templates/eat-restaurant');
    $socials = $branding->socials ?? [];
@endphp
<x-site-layout :tenant="$tenant" :branding="$branding" :template="$template">

    <div x-data="{ mobileNavOpen: false }" class="bg-slate-950 text-slate-100">

        {{-- Nav --}}
        <header class="sticky top-0 z-40 border-b border-slate-800 bg-slate-950/90 backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="#header" class="flex items-center gap-2">
                    @if($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-9 w-auto object-contain">
                    @else
                        <span class="font-display text-lg font-bold text-white">{{ $tenant->name }}</span>
                    @endif
                </a>
                <nav class="hidden gap-8 text-sm font-medium text-slate-300 md:flex">
                    <a href="#header" class="transition hover:text-[var(--tenant-accent)]">Home</a>
                    <a href="#service" class="transition hover:text-[var(--tenant-accent)]">Our Services</a>
                    <a href="#aboutUs" class="transition hover:text-[var(--tenant-accent)]">About Us</a>
                    <a href="#gallery" class="transition hover:text-[var(--tenant-accent)]">Gallery</a>
                    <a href="#feedback" class="transition hover:text-[var(--tenant-accent)]">Feedback</a>
                    <a href="#contact" class="transition hover:text-[var(--tenant-accent)]">Contact</a>
                </nav>
                <button @click="mobileNavOpen = !mobileNavOpen" class="text-slate-300 md:hidden" aria-label="Toggle navigation">
                    <i class="fa" :class="mobileNavOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>
            </div>
            <nav x-cloak x-show="mobileNavOpen" x-transition class="border-t border-slate-800 bg-slate-950 px-4 py-3 md:hidden">
                <a href="#header" @click="mobileNavOpen = false" class="block py-2 text-sm text-slate-300 hover:text-[var(--tenant-accent)]">Home</a>
                <a href="#service" @click="mobileNavOpen = false" class="block py-2 text-sm text-slate-300 hover:text-[var(--tenant-accent)]">Our Services</a>
                <a href="#aboutUs" @click="mobileNavOpen = false" class="block py-2 text-sm text-slate-300 hover:text-[var(--tenant-accent)]">About Us</a>
                <a href="#gallery" @click="mobileNavOpen = false" class="block py-2 text-sm text-slate-300 hover:text-[var(--tenant-accent)]">Gallery</a>
                <a href="#feedback" @click="mobileNavOpen = false" class="block py-2 text-sm text-slate-300 hover:text-[var(--tenant-accent)]">Feedback</a>
                <a href="#contact" @click="mobileNavOpen = false" class="block py-2 text-sm text-slate-300 hover:text-[var(--tenant-accent)]">Contact</a>
            </nav>
        </header>

        {{-- Hero carousel --}}
        <section
            id="header"
            class="relative flex h-[80vh] min-h-[520px] items-center justify-center overflow-hidden scroll-mt-16"
            x-data="{ slide: 0, slides: [
                    { img: '{{ $branding->hero_image_url ?? $assetBase . '/img/slide1.jpg' }}', title: 'We are creative', sub: 'Get started on your next awesome project' },
                    { img: '{{ $assetBase }}/img/slide2.jpg', title: 'We are smart', sub: 'Get started on your next awesome project' },
                    { img: '{{ $assetBase }}/img/slide3.jpg', title: 'We are amazing', sub: 'Get started on your next awesome project' },
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
                    :style="`background-image: linear-gradient(rgba(2,6,23,0.7), rgba(2,6,23,0.7)), url(${s.img})`"
                >
                    <div class="flex h-full flex-col items-center justify-center px-4 text-center">
                        <h1 class="font-display text-4xl font-bold text-white sm:text-5xl" x-text="s.title"></h1>
                        <p class="mt-4 max-w-md text-slate-300" x-text="s.sub"></p>
                        <a href="#service" class="mt-8 rounded-lg bg-[var(--tenant-primary)] px-8 py-3 text-sm font-semibold text-white transition hover:brightness-90">Eat & Chat</a>
                    </div>
                </div>
            </template>

            <div class="absolute bottom-6 left-1/2 flex -translate-x-1/2 gap-2">
                <template x-for="(s, i) in slides" :key="i">
                    <button @click="slide = i" class="h-2.5 w-2.5 rounded-full transition" :class="slide === i ? 'bg-[var(--tenant-accent)]' : 'bg-slate-600'"></button>
                </template>
            </div>
        </section>

        {{-- Services --}}
        <section id="service" class="scroll-mt-16 bg-slate-950 py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-white">Eat & Chat</h2>
                <p class="mx-auto mt-3 max-w-2xl text-slate-400">
                    Fresh ingredients, honest cooking, and a warm room to enjoy it in — every single day.
                </p>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <template x-for="item in [
                        { icon: 'fa-heart', title: 'Streets Best', text: 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat.' },
                        { icon: 'fa-users', title: 'Continental', text: 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat.' },
                        { icon: 'fa-fire', title: 'Daily Dishes', text: 'Morbi vitae tortor tempus, placerat leo et, suscipit lectus. Phasellus ut euismod massa.' },
                        { icon: 'fa-film', title: 'Cookies', text: 'Morbi vitae tortor tempus, placerat leo et, suscipit lectus. Phasellus ut euismod massa.' },
                        { icon: 'fa-cubes', title: 'Cakes', text: 'Morbi vitae tortor tempus, placerat leo et, suscipit lectus. Phasellus ut euismod massa.' },
                        { icon: 'fa-envelope', title: 'Meeting Point', text: 'Morbi vitae tortor tempus, placerat leo et, suscipit lectus. Phasellus ut euismod massa.' },
                    ]" :key="item.title">
                        <div class="rounded-xl border border-slate-800 bg-slate-900 p-6 text-left transition hover:border-[var(--tenant-primary)]/50">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--tenant-primary)]/10 text-xl text-[var(--tenant-primary)]">
                                <i class="fa" :class="item.icon"></i>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-white" x-text="item.title"></h3>
                            <p class="mt-2 text-sm text-slate-400" x-text="item.text"></p>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- About --}}
        <section id="aboutUs" class="scroll-mt-16 bg-slate-900/40 py-20">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-white">Who We Are</h2>
                <div class="mt-6 space-y-4 text-left text-slate-400">
                    <p>{{ $branding->description ?? 'Tell your customers about your business — add a description in your website branding settings.' }}</p>
                </div>
            </div>
        </section>

        {{-- Gallery --}}
        <section id="gallery" class="scroll-mt-16 bg-slate-950 py-20" x-data="{ open: false, active: null, images: [1,2,3,4,5,6,7,8].map(n => '{{ $assetBase }}/img/gallery/img' + n + '.jpg') }">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="text-center">
                    <h2 class="font-display text-3xl font-bold text-white">Our Gallery</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-slate-400">A few plates, a few nights, a few reasons to come back.</p>
                </div>

                <div class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    <template x-for="(src, i) in images" :key="src">
                        <button @click="active = src; open = true" class="group relative aspect-square overflow-hidden rounded-lg border border-slate-800">
                            <img :src="src" alt="Gallery photo" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
                            <span class="absolute inset-0 flex items-center justify-center bg-slate-950/0 text-white opacity-0 transition group-hover:bg-slate-950/50 group-hover:opacity-100">
                                <i class="fa fa-search-plus"></i>
                            </span>
                        </button>
                    </template>
                </div>
            </div>

            <div
                x-cloak x-show="open" x-transition.opacity
                @keydown.escape.window="open = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-4"
                @click.self="open = false"
            >
                <button @click="open = false" class="absolute right-6 top-6 text-2xl text-slate-300 hover:text-white" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
                <img :src="active" alt="Gallery photo enlarged" class="max-h-[85vh] max-w-full rounded-lg object-contain">
            </div>
        </section>

        {{-- Feedback --}}
        <section id="feedback" class="scroll-mt-16 bg-slate-900/40 py-20">
            <div class="mx-auto max-w-3xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-white">What Our Clients Say</h2>
                <div
                    class="mt-12"
                    x-data="{ i: 0, quotes: [
                        { text: 'Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit!', name: 'Someone Famous', initials: 'SF' },
                        { text: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam auctor nec lacus ut tempor.', name: 'Someone Famous', initials: 'SF' },
                        { text: 'Ut rutrum elit in arcu blandit, eget pretium nisl accumsan. Sed ultricies commodo tortor.', name: 'Someone Famous', initials: 'SF' },
                    ] }"
                >
                    <template x-for="(q, idx) in quotes" :key="idx">
                        <blockquote x-show="i === idx" x-transition.opacity class="flex flex-col items-center gap-4">
                            <span class="flex h-16 w-16 items-center justify-center rounded-full bg-[var(--tenant-primary)]/10 text-lg font-semibold text-[var(--tenant-accent)]" x-text="q.initials"></span>
                            <p class="max-w-xl text-lg italic text-slate-300" x-text="q.text"></p>
                            <cite class="not-italic text-sm text-slate-500" x-text="q.name"></cite>
                        </blockquote>
                    </template>
                    <div class="mt-8 flex justify-center gap-2">
                        <template x-for="(q, idx) in quotes" :key="idx">
                            <button @click="i = idx" class="h-2.5 w-2.5 rounded-full transition" :class="i === idx ? 'bg-[var(--tenant-accent)]' : 'bg-slate-700'"></button>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        {{-- Contact --}}
        <section id="contact" class="scroll-mt-16 bg-slate-950 py-20">
            <div class="mx-auto max-w-xl px-4 sm:px-6">
                <div class="text-center">
                    <h2 class="font-display text-3xl font-bold text-white">Contact Us</h2>
                    @if($branding->display_email || $branding->display_phone || $tenant->address)
                        <p class="mx-auto mt-3 max-w-2xl text-slate-400">
                            {{ $branding->display_email }}
                            @if($branding->display_email && $branding->display_phone) &middot; @endif
                            {{ $branding->display_phone }}
                            @if($tenant->address) <br>{{ $tenant->address }} @endif
                        </p>
                    @endif
                    @if($branding->whatsapp_number)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $branding->whatsapp_number) }}" target="_blank" rel="noopener"
                           class="mt-4 inline-flex items-center gap-2 text-sm text-emerald-400 hover:text-emerald-300">
                            <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    @endif
                </div>

                <div x-data="{ sent: false }" class="mt-10">
                    <p x-cloak x-show="sent" class="mb-4 rounded-lg border border-emerald-800 bg-emerald-950/50 px-4 py-3 text-sm text-emerald-400">
                        Your message has been sent. Thank you!
                    </p>
                    <form @submit.prevent="sent = true" class="space-y-4">
                        <input name="name" type="text" required placeholder="Your Name *" class="w-full rounded-lg border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/40">
                        <input name="email" type="email" required placeholder="E-mail address *" class="w-full rounded-lg border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/40">
                        <textarea name="comment" required placeholder="Message *" rows="5" class="w-full rounded-lg border border-slate-700 bg-slate-900 px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/40"></textarea>
                        <button type="submit" class="w-full rounded-lg bg-[var(--tenant-primary)] px-6 py-3 text-sm font-semibold text-white transition hover:brightness-90">Send Message</button>
                    </form>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="border-t border-slate-800 bg-slate-950 py-10">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <a href="#header" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-800 text-slate-400 transition hover:border-[var(--tenant-primary)] hover:text-[var(--tenant-primary)]" aria-label="Back to top">
                    <i class="fa fa-angle-double-up"></i>
                </a>
                @if (array_filter($socials))
                    <ul class="mt-6 flex justify-center gap-4">
                        @foreach (['twitter' => 'fa-twitter', 'facebook' => 'fa-facebook', 'linkedin' => 'fa-linkedin', 'instagram' => 'fa-instagram'] as $platform => $icon)
                            @if (!empty($socials[$platform]))
                                <li><a href="{{ $socials[$platform] }}" target="_blank" rel="noopener" class="flex h-10 w-10 items-center justify-center rounded-full border border-slate-800 text-slate-400 transition hover:border-[var(--tenant-primary)] hover:text-[var(--tenant-primary)]"><i class="fa {{ $icon }}"></i></a></li>
                            @endif
                        @endforeach
                    </ul>
                @endif
                <p class="mt-6 text-xs text-slate-600">&copy; {{ now()->year }} {{ $tenant->name }}. All rights reserved.</p>
            </div>
        </footer>

    </div>

</x-site-layout>
