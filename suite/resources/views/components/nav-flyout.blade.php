@props(['flyoutKey', 'label', 'align' => 'top'])
{{-- `position: fixed` (not the old "absolute left-full") because the icon
     rail this lives in is a scroll container (overflow-y-auto, to fit a
     growing module list) and a CSS-anchored absolute popout would get
     clipped by that container's own scrollport — vertically for tall
     flyouts, even though horizontal overflow happens to escape it. Fixed
     positioning escapes ancestor overflow clipping entirely.

     Position is set IMPERATIVELY (plain DOM, see nav-rail-icon.blade.php's
     click handler) rather than via an Alpine `:style` binding on a shared
     reactive object. Two things were tried and both caused every sibling
     flyout to become simultaneously `display:block` the moment ANY one of
     them was opened: (1) x-teleport to <body>, and (2) keeping this element
     in place but driving `:style` off a shared `mobileFlyoutPos` object.
     The common factor was the shared reactive property itself — something
     about many sibling x-show/x-transition elements all reactively
     depending on the same object appears to desync Alpine's x-show state
     for all of them. Setting `el.style.top/left` directly from the click
     handler, with no shared reactive dependency at all, avoids it. --}}
<div x-show="mobileFlyout === '{{ $flyoutKey }}'" x-cloak x-transition.duration.120ms
     @click.away="mobileFlyout = null"
     data-flyout-align="{{ $align }}"
     class="fixed w-60 max-h-[calc(100vh-2rem)] overflow-y-auto bg-slate-900 border border-slate-800 rounded-2xl shadow-xl p-3 z-50 text-sm">
    <div class="flex items-center justify-between px-2 pb-2 mb-1 border-b border-slate-800/60">
        <span class="text-[11px] font-bold uppercase tracking-wide text-[#D4AF37]">{{ $label }}</span>
        <button type="button" @click="mobileFlyout = null" class="text-slate-500 hover:text-white" aria-label="Close">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    {{ $slot }}
</div>
