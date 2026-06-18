@if (auth()->check() && auth()->user()->tenant?->is_demo)

@php
    $steps = [
        ['label' => 'Dashboard',  'route' => 'dashboard',          'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
        ['label' => 'Booking',    'route' => 'appointments.index',  'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['label' => 'POS',        'route' => 'pos.terminal',        'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
        ['label' => 'Properties', 'route' => 'properties.index',   'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['label' => 'Analytics',  'route' => 'analytics.index',    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
    ];

    $wa  = \App\Models\BillingSetting::get('whatsapp_number') ?? config('contact.whatsapp_number');
    $msg = urlencode(\App\Models\BillingSetting::get('whatsapp_message') ?? config('contact.whatsapp_message'));
@endphp

<div x-data="{ open: true }" x-show="open"
     class="bg-[#0078D4] text-white text-sm px-4 py-2.5 relative z-50">
    <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-3">

        {{-- Left: demo badge + steps --}}
        <div class="flex items-center gap-4 flex-wrap">
            <span class="flex items-center gap-1.5 bg-white/15 px-2.5 py-1 rounded-full text-xs font-semibold shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                Demo Mode
            </span>

            <span class="flex items-center gap-1 text-[#DCEEFA] text-xs shrink-0">
                Explore:
            </span>

            <div class="flex items-center gap-1 overflow-x-auto scrollbar-none">
                @foreach ($steps as $step)
                    @if (Route::has($step['route']))
                    <a href="{{ route($step['route']) }}"
                       class="flex items-center gap-1 px-2.5 py-1 rounded-lg hover:bg-white/15 transition text-xs whitespace-nowrap shrink-0
                              {{ request()->routeIs(str_replace('.index', '.*', $step['route'])) ? 'bg-white/20 font-medium' : 'text-[#E8F2FA]' }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['icon'] }}"/>
                        </svg>
                        {{ $step['label'] }}
                    </a>
                    @endif
                @endforeach
            </div>
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
