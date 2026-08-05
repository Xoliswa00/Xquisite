@props(['template'])

<div x-data="{ open: false, device: 'desktop' }" @keydown.escape.window="open = false" {{ $attributes }}>
    <button type="button" @click="open = true" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium transition-colors">
        Large Preview
    </button>

    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" @click.self="open = false">
        <div class="bg-slate-900 border border-slate-700 rounded-xl w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden">

            <div class="flex items-center justify-between gap-4 px-5 py-3 border-b border-slate-700 shrink-0">
                <p class="text-sm font-semibold text-white truncate">{{ $template->name }} — Live Preview</p>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="flex items-center gap-1 rounded-lg border border-slate-700 p-1">
                        @foreach (['desktop' => 'Desktop', 'tablet' => 'Tablet', 'phone' => 'Phone'] as $key => $label)
                            <button type="button" @click="device = '{{ $key }}'"
                                    class="px-2.5 py-1 rounded text-xs font-medium transition-colors"
                                    :class="device === '{{ $key }}' ? 'bg-[#0078D4] text-white' : 'text-slate-400 hover:text-white'">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    <a href="{{ route('template.preview', $template->key) }}" target="_blank" rel="noopener"
                       class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium whitespace-nowrap">
                        Open full tab ↗
                    </a>
                    <button type="button" @click="open = false" aria-label="Close" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
                </div>
            </div>

            <div class="flex-1 overflow-auto bg-slate-950 p-4 flex justify-center">
                <iframe
                    x-show="open"
                    src="{{ route('template.preview', $template->key) }}"
                    class="border border-slate-700 bg-white h-[75vh] shrink-0 transition-all"
                    :style="device === 'desktop' ? 'width: 100%; max-width: 1280px;' : (device === 'tablet' ? 'width: 768px;' : 'width: 375px;')"
                    loading="lazy"
                ></iframe>
            </div>

        </div>
    </div>
</div>
