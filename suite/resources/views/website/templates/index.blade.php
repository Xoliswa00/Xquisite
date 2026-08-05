@php
    $sortOptions = [
        'featured'   => 'Featured',
        'newest'     => 'Newest',
        'popular'    => 'Popular',
        'price_low'  => 'Price: Low to High',
        'price_high' => 'Price: High to Low',
        'rating'     => 'Top Rated',
        'updated'    => 'Recently Updated',
    ];

    $queryWith = fn (array $overrides) => route('website.templates.index', array_filter(
        array_merge(request()->query(), $overrides),
        fn ($v) => $v !== null && $v !== ''
    ));
@endphp
<x-app-layout>
    <x-slot name="header">Website Marketplace</x-slot>

    <div class="max-w-6xl space-y-6">

        <div>
            <h1 class="text-lg font-semibold text-slate-100">Website Marketplace</h1>
            <p class="text-sm text-slate-400 mt-1 max-w-2xl">
                Pick a template below to launch a public website for your business — no coding required.
                After activating, you'll customize your logo, colors, fonts, and contact details.
                You can switch templates later at any time and your branding carries over automatically.
            </p>
        </div>

        @if (session('success'))
            <div class="px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="px-4 py-3 rounded-xl bg-[#0078D4]/10 border border-[#0078D4]/30 text-[#B8D4F0] text-sm">{{ session('info') }}</div>
        @endif
        @if (session('error'))
            <div class="px-4 py-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm">{{ session('error') }}</div>
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

        {{-- Filters + sort --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ $queryWith(['price' => null]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ !$price ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
                    All
                </a>
                <a href="{{ $queryWith(['price' => 'free']) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ $price === 'free' ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
                    Free
                </a>
                <a href="{{ $queryWith(['price' => 'premium']) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ $price === 'premium' ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
                    Premium
                </a>
                @if ($categories->count() > 1)
                    <span class="text-slate-700">|</span>
                    <a href="{{ $queryWith(['category' => null]) }}"
                       class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ !$category ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
                        All Industries
                    </a>
                    @foreach ($categories as $cat)
                        <a href="{{ $queryWith(['category' => $cat]) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-medium border capitalize transition-colors {{ $category === $cat ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
                            {{ str_replace('-', ' ', $cat) }}
                        </a>
                    @endforeach
                @endif
                <span class="text-slate-700">|</span>
                <a href="{{ $queryWith(['dark_mode' => $darkModeOnly ? null : 1]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ $darkModeOnly ? 'bg-[#0078D4] border-[#0078D4] text-white' : 'border-slate-700 text-slate-400 hover:text-white hover:border-slate-500' }}">
                    Dark Mode Ready
                </a>
            </div>

            <form method="GET" class="shrink-0">
                @foreach (['price', 'category', 'dark_mode'] as $preserve)
                    <input type="hidden" name="{{ $preserve }}" value="{{ request()->query($preserve) }}">
                @endforeach
                <select name="sort" onchange="this.form.submit()"
                        class="bg-slate-800 border border-slate-700 text-slate-200 text-xs rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" {{ $sort === $value ? 'selected' : '' }}>Sort: {{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($templates as $template)
                @php
                    $isActive = $activeTemplate?->key === $template->key;
                    $hasPurchased = $purchasedKeys->contains($template->key);
                @endphp
                <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden flex flex-col">
                    <x-template-preview :template="$template" ratio="aspect-video" />

                    <div class="p-5 flex flex-col flex-1">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="text-sm font-semibold text-white">{{ $template->name }}</h3>
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400 border border-slate-600 font-mono">v{{ $template->version }}</span>
                            @if ($template->is_featured)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#D4AF37]/20 text-[#D4AF37] border border-[#D4AF37]/40">Featured</span>
                            @endif
                            @if ($isActive)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/40">Active</span>
                            @elseif ($hasPurchased)
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-[#0078D4]/20 text-[#B8D4F0] border border-[#0078D4]/40">Owned</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                            <span class="text-amber-400 tracking-tight">{{ $template->starsHtml() }}</span>
                            @if ($template->rating_count)
                                <span>({{ $template->rating_count }})</span>
                            @endif
                            <span>·</span>
                            <span>{{ $template->activeCustomersCount() }} using it</span>
                        </div>

                        <p class="text-xs text-slate-400 flex-1">{{ $template->description }}</p>

                        @if (!empty($template->modules_supported))
                            <div class="flex flex-wrap gap-1 mt-3">
                                @foreach ($template->modules_supported as $moduleKey)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700/60 text-slate-400">{{ str_replace('_', ' ', $moduleKey) }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-xs font-medium {{ $template->isFree() ? 'text-emerald-400' : 'text-slate-500' }}">
                                @if ($template->isFree())
                                    Free
                                @else
                                    R{{ number_format($template->price ?? 0, 0) }} · {{ str_replace('_', ' ', $template->price_type) }}
                                @endif
                            </span>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('template.preview', $template->key) }}" target="_blank" rel="noopener" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium transition-colors">
                                    Live Demo ↗
                                </a>
                                <x-template-preview-modal :template="$template" />
                                <a href="{{ route('website.templates.show', $template) }}" class="text-xs text-[#0078D4] hover:text-[#B8D4F0] font-medium transition-colors">
                                    Full Preview
                                </a>
                            </div>
                        </div>

                        @if ($isActive)
                            <button type="button" disabled class="mt-3 w-full px-4 py-2 bg-slate-700 text-slate-400 text-sm rounded-lg font-medium cursor-not-allowed">
                                Currently Active
                            </button>
                        @elseif ($template->isFree())
                            <form method="POST" action="{{ route('website.templates.activate', $template) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">
                                    {{ $activeTemplate ? 'Switch to This' : 'Activate' }}
                                </button>
                            </form>
                        @elseif ($hasPurchased)
                            <form method="POST" action="{{ route('website.templates.activate', $template) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-[#0078D4] hover:bg-[#0078D4]/90 text-white text-sm rounded-lg font-medium transition-colors">
                                    Activate (Already Purchased)
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('website.templates.checkout', $template) }}" class="mt-3">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-[#D4AF37] hover:bg-[#D4AF37]/90 text-slate-900 text-sm rounded-lg font-semibold transition-colors">
                                    Buy for R{{ number_format($template->price ?? 0, 0) }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if ($templates->isEmpty())
            <div class="bg-slate-800 rounded-xl border border-slate-700 px-6 py-10 text-center text-sm text-slate-400">
                No templates match these filters — try clearing them.
            </div>
        @endif

    </div>
</x-app-layout>
