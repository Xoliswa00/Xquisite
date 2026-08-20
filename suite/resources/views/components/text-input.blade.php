@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-slate-900 border-slate-600 text-white placeholder-slate-500 focus:border-[#0078D4] focus:ring-[#0078D4] rounded-md shadow-sm disabled:opacity-60']) }}>
