@props(['flyoutKey', 'label', 'align' => 'top'])
<div x-show="mobileFlyout === '{{ $flyoutKey }}'" x-cloak x-transition.duration.120ms
     @click.away="mobileFlyout = null"
     class="absolute left-full {{ $align === 'bottom' ? 'bottom-0' : 'top-0' }} ml-2 w-60 max-h-[calc(100vh-2rem)] overflow-y-auto bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-3 z-50 text-sm">
    <div class="flex items-center justify-between px-2 pb-2 mb-1 border-b border-slate-800/60">
        <span class="text-[11px] font-bold uppercase tracking-wide text-[#D4AF37]">{{ $label }}</span>
        <button type="button" @click="mobileFlyout = null" class="text-slate-500 hover:text-white" aria-label="Close">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    {{ $slot }}
</div>
