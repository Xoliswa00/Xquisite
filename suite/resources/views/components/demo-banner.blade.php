@if (auth()->check() && auth()->user()->tenant?->is_demo)

@php
    $wa  = \App\Models\BillingSetting::get('whatsapp_number') ?? config('contact.whatsapp_number');
    $msg = urlencode(\App\Models\BillingSetting::get('whatsapp_message') ?? config('contact.whatsapp_message'));
@endphp

<div x-data="{ open: true }" x-show="open"
     class="bg-[#0078D4] text-white text-sm px-4 py-2.5 relative z-50">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">

        {{-- Left: demo badge --}}
        <div class="flex items-center gap-4 flex-wrap">
            <span class="flex items-center gap-1.5 bg-white/15 px-2.5 py-1 rounded-full text-xs font-semibold shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                Demo Mode
            </span>
            <span class="text-[#DCEEFA] text-xs">
                You're exploring a live demo — use the sidebar to look around.
            </span>
        </div>

        {{-- Right: CTAs --}}
        <div class="flex items-center gap-3">
            @if (session('demo_blocked'))
                <span class="text-amber-200 text-xs font-medium">
                    ⚠ {{ session('demo_blocked') }}
                </span>
            @endif

            <a href="https://wa.me/{{ $wa }}?text={{ $msg }}" target="_blank" rel="noopener"
               class="hidden sm:flex items-center gap-1.5 text-xs text-[#DCEEFA] hover:text-white transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.128.557 4.127 1.528 5.856L.057 23.5l5.793-1.452A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.886 0-3.652-.497-5.18-1.362l-.371-.214-3.439.862.925-3.33-.234-.389A9.955 9.955 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                </svg>
                Chat with us
            </a>

            <a href="{{ route('register') }}"
               class="flex items-center gap-1 bg-white text-[#002B5B] hover:bg-[#F0F7FF] text-xs font-semibold px-3 py-1.5 rounded-lg transition shrink-0">
                Create free account →
            </a>

            <button @click="open = false" class="text-[#B8D4F0] hover:text-white ml-1" aria-label="Dismiss">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

    </div>
</div>

@endif
