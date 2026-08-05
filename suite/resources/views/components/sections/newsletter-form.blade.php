@props(['tenant'])

@if(session('newsletter_success'))
    <p class="text-sm font-medium text-[var(--site-text)]">Thanks — you're on the list!</p>
@else
    <form method="POST" action="{{ route('newsletter.store', $tenant->slug) }}" {{ $attributes->merge(['class' => 'flex flex-col gap-2 sm:flex-row']) }}>
        @csrf
        <input type="email" name="email" required placeholder="you@example.com"
               class="w-full rounded-lg border border-[var(--site-border-2)] bg-[var(--site-bg)] px-4 py-2.5 text-sm text-[var(--site-text)] placeholder-[var(--site-text-faint)] focus:border-[var(--tenant-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]/30">
        <button type="submit" class="shrink-0 rounded-lg bg-[var(--tenant-primary)] px-6 py-2.5 text-sm font-semibold text-white transition hover:brightness-90">Subscribe</button>
    </form>
    @error('email')
        <p class="mt-2 text-xs text-red-500">{{ $message }}</p>
    @enderror
@endif
