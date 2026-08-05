@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-panel border-line-2 text-ink placeholder-ink-faint focus:border-[#0078D4] focus:ring-[#0078D4] rounded-md shadow-sm disabled:opacity-60']) }}>
