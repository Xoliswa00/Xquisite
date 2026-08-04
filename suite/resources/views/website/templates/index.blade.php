<x-app-layout>
    <x-slot name="header">Website Templates</x-slot>

    <div class="max-w-5xl space-y-6">

        <div>
            <h1 class="text-lg font-semibold text-slate-100">Choose a website template</h1>
            <p class="text-sm text-slate-400 mt-1">
                Activate a free template to get a public website live in minutes. You can switch templates
                later without losing your branding.
            </p>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($templates as $template)
                @php $isActive = $activeTemplateKey === $template->key; @endphp
                <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden flex flex-col">
                    <div class="aspect-video bg-slate-900 flex items-center justify-center">
                        @if ($template->preview_image_url)
                            <img src="{{ $template->preview_image_url }}" alt="{{ $template->name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-14 h-14 rounded-lg bg-[#0078D4]/20 flex items-center justify-center">
                                <span class="text-xl font-bold text-[#0078D4]">{{ strtoupper(substr($template->name, 0, 1)) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="p-5 flex flex-col flex-1">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="text-sm font-semibold text-white">{{ $template->name }}</h3>
                            @if ($template->is_featured)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/40">Featured</span>
                            @endif
                            @if ($isActive)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">Active</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 flex-1">{{ $template->description }}</p>

                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-xs font-medium {{ $template->isFree() ? 'text-emerald-400' : 'text-slate-500' }}">
                                @if ($template->isFree())
                                    Free
                                @else
                                    R{{ number_format($template->price ?? 0, 0) }} · {{ str_replace('_', ' ', $template->price_type) }}
                                @endif
                            </span>

                            <a href="{{ route('website.templates.show', $template) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium transition-colors">
                                Preview
                            </a>
                        </div>

                        <form method="POST" action="{{ route('website.templates.activate', $template) }}" class="mt-3">
                            @csrf
                            @if ($isActive)
                                <button type="button" disabled class="w-full px-4 py-2 bg-slate-700 text-slate-400 text-sm rounded-lg font-medium cursor-not-allowed">
                                    Currently Active
                                </button>
                            @elseif ($template->isFree())
                                <button type="submit" class="w-full px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">
                                    Activate
                                </button>
                            @else
                                <button type="button" disabled class="w-full px-4 py-2 bg-slate-700 text-slate-400 text-sm rounded-lg font-medium cursor-not-allowed">
                                    Coming Soon
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($templates->isEmpty())
            <div class="bg-slate-800 rounded-xl border border-slate-700 px-6 py-10 text-center text-sm text-slate-400">
                No templates are available yet — check back soon.
            </div>
        @endif

    </div>
</x-app-layout>
