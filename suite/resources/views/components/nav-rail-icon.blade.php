@props(['flyoutKey', 'label', 'active' => false])
{{-- Position of the sibling <x-nav-flyout> is set imperatively (plain DOM,
     not an Alpine reactive binding) on click — see nav-flyout.blade.php for
     why a shared reactive position object caused every flyout to become
     visible at once. $el.parentElement is the shared `<div class="relative">`
     wrapper this button and its flyout both live in. --}}
<button type="button"
        @click="
            if (mobileFlyout === @js($flyoutKey)) {
                mobileFlyout = null;
            } else {
                let r = $el.getBoundingClientRect();
                let fly = $el.parentElement.querySelector('[data-flyout-align]');
                if (fly) {
                    fly.style.left = (r.right + 8) + 'px';
                    if (fly.dataset.flyoutAlign === 'bottom') {
                        fly.style.top = 'auto';
                        fly.style.bottom = (window.innerHeight - r.bottom) + 'px';
                    } else {
                        fly.style.bottom = 'auto';
                        fly.style.top = r.top + 'px';
                    }
                }
                mobileFlyout = @js($flyoutKey);
            }
        "
        class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'text-white' : 'text-slate-400' }}"
        :class="mobileFlyout === @js($flyoutKey) ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 hover:text-white'"
        aria-label="{{ $label }}" title="{{ $label }}">
    <span class="w-5 h-5">{{ $slot }}</span>
</button>
