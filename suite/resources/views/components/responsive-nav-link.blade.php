@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-[#0078D4] text-start text-base font-medium text-[#002B5B] bg-[#F0F7FF] focus:outline-none focus:text-[#002B5B] focus:bg-[#E6F2FB] focus:border-[#002B5B] transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 hover:text-[#002B5B] hover:bg-[#F5F7FA] hover:border-gray-300 focus:outline-none focus:text-[#002B5B] focus:bg-[#F5F7FA] focus:border-gray-300 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
