@props(['content', 'variant' => null, 'branding', 'tenant', 'template', 'editing' => false])

@php
    $variant = $variant ?? 'inline';
    $hasBooking = $tenant->hasModule('booking');
@endphp

@if($hasBooking)
    <section class="bg-[var(--site-surface)] py-20">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6">
            @if($content['heading'] ?? null)
                <h2 class="font-display text-3xl font-bold text-[var(--site-text)]">{{ $content['heading'] }}</h2>
            @endif
            @if($content['subtext'] ?? null)
                <p class="mx-auto mt-3 max-w-2xl text-[var(--site-text-muted)]">{{ $content['subtext'] }}</p>
            @endif
            <div class="mt-8 {{ $variant === 'card' ? 'rounded-xl border border-[var(--site-border)] bg-[var(--site-bg)] p-8' : '' }}">
                <a href="{{ route('book.index', $tenant->slug) }}"
                   class="inline-block rounded-lg bg-[var(--tenant-primary)] px-8 py-3 text-sm font-semibold text-white transition hover:brightness-90">
                    Book an Appointment
                </a>
            </div>
        </div>
    </section>
@elseif($editing)
    <section class="border-2 border-dashed border-[var(--site-border-2)] bg-[var(--site-surface)] py-16">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6">
            <p class="text-sm text-[var(--site-text-muted)]">This Booking Form section is hidden from visitors until Bookings is active.</p>
            <a href="{{ route('settings.modules.index') }}" class="mt-3 inline-block text-sm font-medium text-[var(--tenant-primary)] hover:underline">Activate Bookings →</a>
        </div>
    </section>
@endif
