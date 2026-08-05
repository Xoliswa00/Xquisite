@php
    $assetBase = asset('site-templates/lovely-wedding');
    $socials = $branding->socials ?? [];
    $heroImage = $branding->hero_image_url ?? $assetBase . '/img/main2.jpg';
    $aboutImage = $branding->about_image_url ?? $assetBase . '/img/about2.png';
    $customGallery = !empty($branding->gallery_images) ? array_values($branding->gallery_images) : null;
@endphp
<x-site-layout :tenant="$tenant" :branding="$branding" :template="$template">

    <div x-data="{ mobileNavOpen: false }" class="bg-[var(--site-bg)] text-[var(--site-text)]">

        {{-- Nav --}}
        <header class="sticky top-0 z-40 border-b border-[var(--site-border)] bg-[var(--site-nav-bg)] backdrop-blur">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
                <a href="#hero_section" class="flex shrink-0 items-center gap-2">
                    @if($tenant->logo_url)
                        <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="h-9 w-auto object-contain">
                    @else
                        <span class="font-display text-lg font-bold text-[var(--site-text)]">{{ $tenant->name }}</span>
                    @endif
                </a>
                <nav class="hidden gap-7 text-sm font-medium text-[var(--site-text)] md:flex">
                    <a href="#hero_section" class="transition hover:text-[var(--tenant-primary)]">Home</a>
                    <a href="#aboutUs" class="transition hover:text-[var(--tenant-primary)]">About Us</a>
                    <a href="#service" class="transition hover:text-[var(--tenant-primary)]">Packages</a>
                    <a href="#gallery" class="transition hover:text-[var(--tenant-primary)]">Gallery</a>
                    <a href="#team" class="transition hover:text-[var(--tenant-primary)]">Our Team</a>
                    <a href="#contact" class="transition hover:text-[var(--tenant-primary)]">Contact</a>
                </nav>
                <button @click="mobileNavOpen = !mobileNavOpen" class="shrink-0 text-[var(--site-text)] md:hidden" aria-label="Toggle navigation">
                    <i class="fa" :class="mobileNavOpen ? 'fa-times' : 'fa-bars'"></i>
                </button>
            </div>
            <nav x-cloak x-show="mobileNavOpen" x-transition class="border-t border-[var(--site-border)] bg-[var(--site-bg)] px-4 py-3 md:hidden">
                <a href="#hero_section" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Home</a>
                <a href="#aboutUs" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">About Us</a>
                <a href="#service" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Packages</a>
                <a href="#gallery" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Gallery</a>
                <a href="#team" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Our Team</a>
                <a href="#contact" @click="mobileNavOpen = false" class="block py-2 text-sm text-[var(--site-text)] hover:text-[var(--tenant-primary)]">Contact</a>
            </nav>
        </header>

        {{-- Hero (photo background — dark overlay + white text by design) --}}
        <section id="hero_section" class="relative flex min-h-[80vh] items-center justify-center overflow-hidden bg-cover bg-center text-center"
                 style="background-image: linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.65)), url('{{ $heroImage }}')">
            <div class="px-4">
                <p class="text-sm font-semibold uppercase tracking-[0.3em] text-[var(--tenant-accent)]">Wedding &amp; Event Planning</p>
                <h1 class="mt-3 font-display text-4xl font-extrabold text-white sm:text-5xl">{{ $tenant->name }}</h1>
                <p class="mx-auto mt-4 max-w-lg text-white/80">{{ $branding->description ?? "We plan the day you've been dreaming of, down to the last detail." }}</p>
                <a href="#contact" class="mt-8 inline-block rounded-lg bg-[var(--tenant-primary)] px-8 py-3 text-sm font-semibold text-white transition hover:brightness-90">Book a Consultation</a>
            </div>
        </section>

        {{-- About --}}
        <section id="aboutUs" class="scroll-mt-16 bg-[var(--site-surface)] py-20">
            <div class="mx-auto grid max-w-6xl gap-12 px-4 sm:px-6 md:grid-cols-2 md:items-center">
                <img src="{{ $aboutImage }}" alt="{{ $tenant->name }}" class="rounded-xl">
                <div>
                    <h3 class="font-display text-2xl font-bold text-[var(--site-text)]">About Us</h3>
                    <p class="mt-4 text-[var(--site-text-muted)]">{{ $branding->description ?? 'Tell your customers about your business — add a description in your website branding settings.' }}</p>
                    <ul class="mt-6 space-y-2">
                        <template x-for="label in ['Full wedding planning', 'Day-of coordination', 'Venue sourcing', 'Vendor management', 'Bridal styling']" :key="label">
                            <li class="flex items-center gap-2 text-[var(--site-text)]">
                                <i class="fa fa-angle-double-right text-[var(--tenant-accent)]"></i>
                                <span x-text="label"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </section>

        {{-- Services / Packages --}}
        <section id="service" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Our Packages</h2>
                <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">From an intimate ceremony to a full weekend of celebrations, we tailor every package to you.</p>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="item in [
                        { icon: 'fa-glass', title: 'Engagement Party', text: 'A relaxed celebration to mark the beginning of your journey together.' },
                        { icon: 'fa-institution', title: 'Full Wedding', text: 'End-to-end planning — venue, vendors, styling, and timeline, fully managed.' },
                        { icon: 'fa-cutlery', title: 'Reception Dinner', text: 'A reception that feels as good as it looks, from seating to the last dance.' },
                        { icon: 'fa-heart', title: 'Ceremony Coordination', text: 'On-the-day coordination so you can be fully present for your own wedding.' },
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

        {{-- Gallery --}}
        @php
            $galleryImages = $customGallery ?? collect(range(1, 8))->map(fn ($n) => "{$assetBase}/img/portfolio_pic{$n}.jpg")->all();
        @endphp
        <section id="gallery" class="scroll-mt-16 bg-[var(--site-surface)] py-20" x-data="{ open: false, active: null, images: {{ \Illuminate\Support\Js::from($galleryImages) }} }">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="text-center">
                    <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Photo Gallery</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">Moments from weddings we've had the privilege of planning.</p>
                </div>

                <div class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    <template x-for="(src, i) in images" :key="src">
                        <button @click="active = src; open = true" class="group relative aspect-square overflow-hidden rounded-lg border border-[var(--site-border)]">
                            <img :src="src" alt="Wedding gallery photo" class="h-full w-full object-cover transition duration-300 group-hover:scale-110">
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
                @click.self="open = false"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4"
            >
                <button @click="open = false" class="absolute right-6 top-6 text-2xl text-white/80 hover:text-white" aria-label="Close">
                    <i class="fa fa-times"></i>
                </button>
                <img :src="active" alt="Gallery photo enlarged" class="max-h-[85vh] max-w-full rounded-lg object-contain">
            </div>
        </section>

        {{-- Team --}}
        <section id="team" class="scroll-mt-16 bg-[var(--site-bg)] py-20">
            <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Our Team</h2>
                <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">The people who bring every detail of your day together.</p>

                <div class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <template x-for="t in [
                        { img: '{{ $assetBase }}/img/team01.jpg', name: 'Rosy Illue', role: 'Lead Planner' },
                        { img: '{{ $assetBase }}/img/team02.jpg', name: 'Chrislke Moyo', role: 'Floral Designer' },
                        { img: '{{ $assetBase }}/img/team03.jpg', name: 'Mike Reiln', role: 'Day-of Coordinator' },
                        { img: '{{ $assetBase }}/img/team04.jpg', name: 'Dennisel Cruz', role: 'Bridal Stylist' },
                    ]" :key="t.name">
                        <div>
                            <img :src="t.img" :alt="t.name" class="mx-auto h-28 w-28 rounded-full border border-[var(--site-border)] object-cover">
                            <h4 class="mt-4 text-sm font-semibold text-[var(--site-text)]" x-text="t.name"></h4>
                            <span class="text-xs text-[var(--site-text-faint)]" x-text="t.role"></span>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        {{-- Contact --}}
        <footer id="contact" class="scroll-mt-16 border-t border-[var(--site-border)] bg-[var(--site-surface)] py-20">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="text-center">
                    <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">Contact Us</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">Tell us a little about your day and we'll be in touch.</p>
                </div>

                <div class="mt-10 grid gap-10 md:grid-cols-3">
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
                        @if (array_filter($socials))
                            <ul class="mt-6 flex gap-3 text-[var(--site-text-faint)]">
                                @foreach (['facebook' => 'fa-facebook', 'twitter' => 'fa-twitter', 'instagram' => 'fa-instagram', 'linkedin' => 'fa-linkedin'] as $platform => $icon)
                                    @if (!empty($socials[$platform]))
                                        <li><a href="{{ $socials[$platform] }}" target="_blank" rel="noopener" class="hover:text-[var(--tenant-primary)]"><i class="fa {{ $icon }}"></i></a></li>
                                    @endif
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <div class="md:col-span-2" x-data="{ sent: false }">
                        <p x-cloak x-show="sent" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            Thanks — your message has been sent.
                        </p>
                        <form @submit.prevent="sent = true" class="grid gap-4 sm:grid-cols-2">
                            <input type="text" name="name" required placeholder="Your Name *" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30">
                            <input type="email" name="email" required placeholder="Your E-mail *" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30">
                            <textarea name="message" required rows="6" placeholder="Tell us about your day *" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30 sm:col-span-2"></textarea>
                            <button type="submit" class="rounded-lg bg-[var(--tenant-primary)] px-6 py-3 text-sm font-semibold text-white transition hover:brightness-90 sm:col-span-2">Send Message</button>
                        </form>
                    </div>
                </div>

                <p class="mt-16 text-center text-xs text-[var(--site-text-faint)]">&copy; {{ now()->year }} {{ $tenant->name }}. All rights reserved.</p>
            </div>
        </footer>

    </div>

</x-site-layout>
