<x-app-layout>
    <x-slot name="header">Website</x-slot>

    <div class="max-w-5xl space-y-6" x-data="{ filter: 'all' }">

        <div>
            <h1 class="text-lg font-semibold text-slate-100">Your Website</h1>
            <p class="text-sm text-slate-400 mt-1 max-w-2xl">
                Pick a template below to launch a public website for your business — no coding required.
                After activating, you'll customize your logo, colors, fonts, and contact details.
                You can switch templates later at any time and your branding carries over automatically.
            </p>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Current site status --}}
        @if ($activeTemplate)
            <div class="rounded-xl border border-emerald-800/50 bg-emerald-950/20 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                <x-template-preview :template="$activeTemplate" ratio="aspect-video" class="w-32 shrink-0 border border-slate-700" />
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-400">Your website is live</p>
                    <p class="text-sm font-medium text-white mt-0.5">{{ $activeTemplate->name }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $tenant->website_url }}</p>
                </div>
                <div class="flex gap-2 shrink-0 flex-wrap">
                    <a href="{{ $tenant->website_url }}" target="_blank" rel="noopener"
                       class="px-4 py-2 rounded-lg border border-slate-700 text-slate-200 text-sm font-medium hover:border-slate-500 transition-colors">
                        View Live Site
                    </a>
                    <a href="{{ route('website.analytics') }}"
                       class="px-4 py-2 rounded-lg border border-slate-700 text-slate-200 text-sm font-medium hover:border-slate-500 transition-colors">
                        Analytics
                    </a>
                    <a href="{{ route('website.branding.edit') }}"
                       class="px-4 py-2 rounded-lg bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm font-medium transition-colors">
                        Edit Branding
                    </a>
                </div>
            </div>
        @else
            <div class="rounded-xl border border-slate-700 bg-slate-800/60 p-5">
                <p class="text-sm text-slate-300">You haven't activated a website yet — pick a template below to get started.</p>
            </div>
        @endif

        {{-- Category filter --}}
        @if ($categories->count() > 1)
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="filter = 'all'"
                        class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors"
                        :class="filter === 'all' ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500'">
                    All
                </button>
                @foreach ($categories as $category)
                    <button type="button" @click="filter = '{{ $category }}'"
                            class="px-3 py-1.5 rounded-full text-xs font-medium border capitalize transition-colors"
                            :class="filter === '{{ $category }}' ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500'">
                        {{ str_replace('-', ' ', $category) }}
                    </button>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($templates as $template)
                @php $isActive = $activeTemplate?->key === $template->key; @endphp
                <div x-show="filter === 'all' || filter === '{{ $template->category }}'" class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden flex flex-col">
                    <x-template-preview :template="$template" ratio="aspect-video" />

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
                                Full Preview
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
                                    {{ $activeTemplate ? 'Switch to This' : 'Activate' }}
                                </button>
                            @else
                                <button type="button" disabled title="Paid templates aren't available yet" class="w-full px-4 py-2 bg-slate-700 text-slate-400 text-sm rounded-lg font-medium cursor-not-allowed">
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
