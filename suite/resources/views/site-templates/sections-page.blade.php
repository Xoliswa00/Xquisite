@php
    $editing = $editing ?? false;
@endphp
<x-site-layout :tenant="$tenant" :branding="$branding" :template="$template">

    <div class="bg-[var(--site-bg)] text-[var(--site-text)]">

        <x-site.nav :tenant="$tenant" :sections="$sections" />

        @foreach ($sections as $section)
            @continue(! $section->is_visible && ! $editing)
            <div
                id="section-{{ $section->id }}"
                @if($editing) data-xq-section="{{ $section->id }}" class="relative group/xq-section scroll-mt-16" @else class="scroll-mt-16" @endif
            >
                @if($editing)
                    <x-editor.section-hover-toolbar :section="$section" />
                    @unless($section->is_visible)
                        <div class="pointer-events-none absolute inset-0 z-10 bg-white/60 dark:bg-black/50"></div>
                        <span class="absolute left-3 top-3 z-20 rounded bg-slate-900/80 px-2 py-0.5 text-xs font-medium text-white">Hidden</span>
                    @endunless
                @endif

                <x-dynamic-component
                    :component="'sections.' . str_replace('_', '-', $section->type)"
                    :content="$section->content"
                    :variant="$section->resolvedVariant()"
                    :branding="$branding"
                    :tenant="$tenant"
                    :template="$template"
                    :editing="$editing"
                />
            </div>
        @endforeach

        <x-site.footer :tenant="$tenant" :branding="$branding" :sections="$sections" />

    </div>

</x-site-layout>
