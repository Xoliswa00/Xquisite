@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'split';
    $email = $content['email'] ?? $branding->display_email ?? null;
    $phone = $content['phone'] ?? $branding->display_phone ?? null;
    $whatsapp = $content['whatsapp_number'] ?? $branding->whatsapp_number ?? null;
    $address = $content['address'] ?? $tenant->address ?? null;
    $hours = collect($content['business_hours'] ?? []);
    $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
@endphp

<section class="bg-[var(--site-bg)] py-20">
    <div class="mx-auto {{ $variant === 'split' ? 'max-w-6xl' : 'max-w-xl' }} px-4 sm:px-6">
        <div class="text-center">
            <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] ?? 'Contact Us' }}</h2>
            @if($content['subtext'] ?? null)
                <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
            @endif
        </div>

        <div class="mt-10 {{ $variant === 'split' ? 'grid gap-10 md:grid-cols-3' : '' }}">
            @if($variant === 'split')
                <div class="rounded-xl border border-[var(--site-border)] bg-[var(--site-surface)] p-6">
                    <h3 class="font-semibold text-[var(--site-text)]">Contact Info</h3>
                    <address class="mt-4 not-italic text-sm leading-relaxed text-[var(--site-text-muted)]">
                        <strong class="text-[var(--site-text)]">{{ $tenant->name }}</strong><br>
                        @if($address) {{ $address }}<br> @endif
                        @if($phone) <abbr title="Phone">P:</abbr> {{ $phone }}<br> @endif
                        @if($email) {{ $email }} @endif
                    </address>

                    @if($hours->isNotEmpty())
                        <div class="mt-4 space-y-1 text-xs text-[var(--site-text-muted)]">
                            @foreach($days as $key => $label)
                                @php $day = $hours[$key] ?? null; @endphp
                                @if($day)
                                    <div class="flex justify-between gap-4">
                                        <span>{{ $label }}</span>
                                        <span>{{ !empty($day['closed']) ? 'Closed' : trim(($day['open'] ?? '') . ' – ' . ($day['close'] ?? '')) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    @if($whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener"
                           class="mt-4 inline-flex items-center gap-2 text-sm text-emerald-600 hover:text-emerald-700">
                            <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    @endif
                </div>
            @else
                @if($whatsapp)
                    <div class="text-center">
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $whatsapp) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 text-sm text-emerald-600 hover:text-emerald-700">
                            <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
                        </a>
                    </div>
                @endif
            @endif

            <div class="{{ $variant === 'split' ? 'md:col-span-2' : 'mt-6' }}" x-data="{ sent: false }">
                <p x-cloak x-show="sent" class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    Thanks — your message has been sent.
                </p>
                <form @submit.prevent="sent = true" class="grid gap-4 sm:grid-cols-2">
                    <input type="text" name="name" required placeholder="Your Name *" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30">
                    <input type="email" name="email" required placeholder="E-mail address *" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30">
                    <textarea name="message" required rows="6" placeholder="Message *" class="rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-3 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30 sm:col-span-2"></textarea>
                    <button type="submit" class="rounded-lg bg-[var(--tenant-primary)] px-6 py-3 text-sm font-semibold text-white transition hover:brightness-90 sm:col-span-2">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>
