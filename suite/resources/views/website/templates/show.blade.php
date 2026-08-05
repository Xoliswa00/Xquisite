<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('website.templates.index') }}" class="text-slate-400 hover:text-white transition-colors">← Templates</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-lg font-semibold text-slate-100">{{ $template->name }}</h2>
        </div>
    </x-slot>

    @php $isActive = $activeTemplateKey === $template->key; @endphp

    <div class="max-w-3xl space-y-6">

        <x-template-preview :template="$template" ratio="aspect-video" class="border border-slate-700" />
        <p class="text-xs text-slate-500 -mt-2">
            Live preview with sample content — your actual site will show your business name, logo, and colors once activated.
        </p>

        <div class="bg-slate-800 rounded-xl border border-slate-700 p-6">
            <div class="flex items-center gap-2 flex-wrap mb-2">
                <h1 class="text-base font-semibold text-white">{{ $template->name }}</h1>
                @if ($template->is_featured)
                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/40">Featured</span>
                @endif
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-300 border border-slate-600 capitalize">{{ str_replace('-', ' ', $template->category) }}</span>
            </div>
            <p class="text-sm text-slate-400">{{ $template->description }}</p>

            <div class="mt-5 flex items-center justify-between">
                <span class="text-sm font-medium {{ $template->isFree() ? 'text-emerald-400' : 'text-slate-500' }}">
                    @if ($template->isFree())
                        Free
                    @else
                        R{{ number_format($template->price ?? 0, 0) }} · {{ str_replace('_', ' ', $template->price_type) }}
                    @endif
                </span>

                <form method="POST" action="{{ route('website.templates.activate', $template) }}">
                    @csrf
                    @if ($isActive)
                        <button type="button" disabled class="px-5 py-2 bg-slate-700 text-slate-400 text-sm rounded-lg font-medium cursor-not-allowed">
                            Currently Active
                        </button>
                    @elseif ($template->isFree())
                        <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">
                            Activate This Template
                        </button>
                    @else
                        <button type="button" disabled class="px-5 py-2 bg-slate-700 text-slate-400 text-sm rounded-lg font-medium cursor-not-allowed">
                            Coming Soon
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
