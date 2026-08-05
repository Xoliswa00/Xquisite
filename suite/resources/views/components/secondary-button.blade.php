<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-panel-2 border border-line-2 rounded-md font-semibold text-xs text-ink uppercase tracking-widest shadow-sm hover:bg-line-2 focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:ring-offset-2 focus:ring-offset-panel disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
