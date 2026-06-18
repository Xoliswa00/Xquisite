<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#002B5B] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#003872] focus:bg-[#003872] active:bg-[#001A3A] focus:outline-none focus:ring-2 focus:ring-[#0078D4] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
