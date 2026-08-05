@props(['template', 'ratio' => 'aspect-video'])

<div {{ $attributes->merge(['class' => "$ratio relative overflow-hidden rounded-lg bg-slate-900"]) }}>
    <iframe
        src="{{ route('template.preview', $template->key) }}"
        class="pointer-events-none absolute left-0 top-0 border-0"
        style="width:400%; height:400%; transform:scale(0.25); transform-origin:0 0;"
        loading="lazy"
        tabindex="-1"
        aria-hidden="true"
        scrolling="no"
    ></iframe>
</div>
