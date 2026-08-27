@props(['label', 'active' => false])
<div x-data="{ open: {{ $active ? 'true' : 'false' }} }" class="pt-2 border-t border-slate-800 mt-2">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between gap-2 px-3 py-1.5 rounded-lg hover:bg-slate-800/50">
        <span class="flex items-center gap-3 text-xs text-[#D4AF37] uppercase tracking-wide font-bold">
            <span class="w-4 h-4 shrink-0">{{ $icon }}</span>
            {{ $label }}
        </span>
        <svg class="w-3.5 h-3.5 text-slate-500 shrink-0 transition-transform duration-150" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </button>
    <div x-show="open" x-transition.duration.150ms class="mt-0.5">
        {{ $slot }}
    </div>
</div>
