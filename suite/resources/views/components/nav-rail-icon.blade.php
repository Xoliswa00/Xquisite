@props(['flyoutKey', 'label', 'active' => false])
<button type="button" @click="mobileFlyout = (mobileFlyout === '{{ $flyoutKey }}' ? null : '{{ $flyoutKey }}')"
        class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'text-white' : 'text-slate-400' }}"
        :class="mobileFlyout === '{{ $flyoutKey }}' ? 'bg-slate-800 text-white' : 'hover:bg-slate-800 hover:text-white'"
        aria-label="{{ $label }}" title="{{ $label }}">
    <span class="w-5 h-5">{{ $slot }}</span>
</button>
