<x-app-layout>
    <x-slot name="header">Website Templates</x-slot>

    <div class="space-y-8">

        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-xl font-bold text-[#D4AF37]">Website Templates</h1>
                <p class="mt-1 text-sm text-slate-400 max-w-2xl">
                    This is the catalog tenants pick from at <span class="font-mono text-xs text-slate-300">/website/templates</span>.
                    Archiving hides a template from new activations without breaking sites already using it; Hide/Unhide controls whether it's shown in the catalog at all.
                </p>
            </div>
            <a href="{{ route('admin.templates.create') }}"
               class="shrink-0 px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm rounded-lg font-medium transition-colors">
                + Add Template
            </a>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        @forelse ($templates as $category => $group)

            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-[#0078D4]/20 text-[#B8D4F0] capitalize">
                        {{ str_replace('-', ' ', $category) }}
                    </span>
                    <span class="text-sm text-slate-500">{{ $group->count() }} {{ Str::plural('template', $group->count()) }}</span>
                </div>

                <div class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
                    @foreach ($group as $template)
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-700/30 transition-colors">

                        <x-template-preview :template="$template" ratio="aspect-video" class="w-28 shrink-0 hidden sm:block border border-slate-700" />

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-medium text-white text-sm">{{ $template->name }}</p>
                                @if ($template->is_featured)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/40">Featured</span>
                                @endif
                                @if (!$template->is_active)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 border border-slate-600">Archived</span>
                                @endif
                                @if (!$template->is_visible)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 border border-slate-600">Hidden</span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $template->description }}</p>
                            <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-500">
                                <span class="font-mono">v{{ $template->version }}</span>
                                <span class="text-amber-400">{{ $template->starsHtml() }}</span>
                                @if ($template->rating_count)
                                    <span>({{ $template->rating_count }})</span>
                                @endif
                                <span>{{ $template->activeCustomersCount() }} using it</span>
                                @if ($template->speed_score || $template->seo_score || $template->accessibility_score)
                                    <span>Speed {{ $template->speed_score ?? '—' }} · SEO {{ $template->seo_score ?? '—' }} · A11y {{ $template->accessibility_score ?? '—' }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="hidden sm:flex items-center gap-6 text-sm text-slate-400 shrink-0">
                            <span class="font-mono text-xs text-slate-500">{{ $template->key }}</span>
                            <span>
                                @if ($template->isFree())
                                    Free
                                @else
                                    R{{ number_format($template->price ?? 0, 0) }} · {{ str_replace('_', ' ', $template->price_type) }}
                                @endif
                            </span>
                        </div>

                        {{-- Quick toggles --}}
                        <div class="flex items-center gap-2 shrink-0">
                            <form method="POST" action="{{ route('admin.templates.status', $template) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $template->is_active ? 0 : 1 }}">
                                <input type="hidden" name="is_visible" value="{{ $template->is_visible ? 1 : 0 }}">
                                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-600 text-slate-300 hover:border-[#0078D4] hover:text-white transition-colors">
                                    {{ $template->is_active ? 'Archive' : 'Reactivate' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.templates.status', $template) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="is_active" value="{{ $template->is_active ? 1 : 0 }}">
                                <input type="hidden" name="is_visible" value="{{ $template->is_visible ? 0 : 1 }}">
                                <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-600 text-slate-300 hover:border-[#0078D4] hover:text-white transition-colors">
                                    {{ $template->is_visible ? 'Hide' : 'Unhide' }}
                                </button>
                            </form>
                        </div>

                        <a href="{{ route('admin.templates.edit', $template) }}"
                           class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium shrink-0 transition-colors">
                            Edit
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>

        @empty
            <div class="bg-slate-800 rounded-xl border border-slate-700 px-6 py-10 text-center text-sm text-slate-400">
                No templates in the catalog yet.
            </div>
        @endforelse

    </div>
</x-app-layout>
