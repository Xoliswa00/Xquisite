<x-app-layout>
    <x-slot name="header">Booking Menu</x-slot>

    <div class="max-w-5xl mx-auto space-y-8">

        {{-- Hero --}}
        <div class="rounded-2xl bg-gradient-to-br from-slate-900 to-[#001A3A] border border-slate-800 px-8 py-10">
            <h1 class="text-3xl font-bold text-[#D4AF37]">Our Services</h1>
            <p class="text-slate-400 mt-2">Browse services, combos, and active promotions.</p>
        </div>

        {{-- ── ACTIVE PROMOTIONS ─────────────────────────────────────────────── --}}
        @if($promotions->isNotEmpty())
        <div>
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">🎉 Active Promotions</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($promotions as $promo)
                <div class="bg-gradient-to-br from-amber-900/40 to-orange-900/30 border border-amber-700/50 rounded-xl p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-white">{{ $promo->name }}</p>
                            @if($promo->description)
                                <p class="text-sm text-slate-400 mt-0.5">{{ $promo->description }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-lg font-black text-amber-400">
                            {{ $promo->discount_type === 'percentage' ? $promo->discount_value . '% OFF' : 'R' . number_format($promo->discount_value, 2) . ' OFF' }}
                        </span>
                    </div>
                    <div class="mt-3 flex items-center gap-3 flex-wrap">
                        <div class="flex items-center gap-2 bg-slate-900/60 border border-amber-800/50 rounded-lg px-3 py-1.5">
                            <span class="text-xs text-slate-400">Code:</span>
                            <code class="font-bold text-amber-300 text-sm tracking-wider">{{ $promo->code }}</code>
                        </div>
                        @if($promo->valid_until)
                            <span class="text-xs text-slate-400">Expires {{ $promo->valid_until->format('d M Y') }}</span>
                        @endif
                        @if($promo->max_uses)
                            <span class="text-xs text-slate-400">{{ $promo->max_uses - $promo->used_count }} uses left</span>
                        @endif
                        <a href="{{ route('promotions.edit', $promo) }}" class="ml-auto text-xs text-slate-500 hover:text-white">Edit →</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── COMBOS ────────────────────────────────────────────────────────── --}}
        @if($combos->isNotEmpty())
        <div>
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-3">✨ Service Combos</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($combos as $combo)
                <div class="bg-slate-900 border border-[#002B5B]/60 rounded-xl overflow-hidden">
                    <div class="h-1 bg-[#0078D4]"></div>
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <p class="font-semibold text-white">{{ $combo->name }}</p>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-slate-500 line-through">R{{ number_format($combo->total_service_price, 2) }}</p>
                                <p class="font-bold text-white">R{{ number_format($combo->combo_price, 2) }}</p>
                                <p class="text-xs text-emerald-400">Save R{{ number_format($combo->savings, 2) }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            @foreach($combo->services as $svc)
                                <span class="text-xs bg-slate-800 text-slate-300 px-2 py-0.5 rounded-full">
                                    {{ $svc->name }} · {{ $svc->duration_minutes }}min
                                </span>
                            @endforeach
                        </div>
                        @if($combo->description)
                            <p class="text-xs text-slate-500">{{ $combo->description }}</p>
                        @endif
                        <div class="mt-3 flex justify-end">
                            <a href="{{ route('combos.edit', $combo) }}" class="text-xs text-slate-500 hover:text-white">Edit →</a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ── SERVICES BY CATEGORY ──────────────────────────────────────────── --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Services</h2>

                {{-- Category filter --}}
                <div class="flex gap-2 flex-wrap">
                    <a href="{{ route('booking.menu') }}"
                       class="px-3 py-1 rounded-full text-xs font-medium transition-colors {{ !$categoryFilter ? 'bg-[#0078D4] text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                        All
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('booking.menu', ['category' => $cat->id]) }}"
                           class="px-3 py-1 rounded-full text-xs font-medium transition-colors {{ $categoryFilter == $cat->id ? 'bg-[#0078D4] text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                            {{ $cat->icon }} {{ $cat->name }}
                        </a>
                    @endforeach
                    @if($uncategorized->count())
                        <a href="{{ route('booking.menu', ['category' => 'uncategorized']) }}"
                           class="px-3 py-1 rounded-full text-xs font-medium transition-colors {{ $categoryFilter === 'uncategorized' ? 'bg-[#0078D4] text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                            Other
                        </a>
                    @endif
                </div>
            </div>

            @foreach($categories as $cat)
                @if(!$categoryFilter || $categoryFilter == $cat->id)
                    @if($cat->activeServices->count())
                        @php $classes = \App\Models\ServiceCategory::colorClasses()[$cat->color] ?? []; @endphp
                        <div class="mb-8">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="text-xl">{{ $cat->icon }}</span>
                                <h3 class="text-base font-semibold text-[#D4AF37]">{{ $cat->name }}</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($cat->activeServices as $service)
                                    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden hover:border-slate-600 transition-colors">
                                        <div class="h-1.5 {{ $classes['dot'] ?? 'bg-slate-500' }}"></div>
                                        <div class="p-4">
                                            <p class="font-semibold text-white">{{ $service->name }}</p>
                                            @if($service->description)
                                                <p class="text-sm text-slate-400 mt-1 line-clamp-2">{{ $service->description }}</p>
                                            @endif
                                            <div class="flex items-center justify-between mt-3">
                                                <span class="font-bold text-white">R{{ number_format($service->price, 2) }}</span>
                                                <div class="flex items-center gap-2">
                                                    @if($service->duration_minutes)
                                                        <span class="text-xs text-slate-400 bg-slate-800 px-2 py-1 rounded">{{ $service->duration_minutes }}min</span>
                                                    @endif
                                                    @if($service->cost_price)
                                                        <span class="text-xs text-slate-500">cost R{{ number_format($service->cost_price, 2) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            @endforeach

            {{-- Uncategorized --}}
            @if((!$categoryFilter || $categoryFilter === 'uncategorized') && $uncategorized->count())
                <div class="mb-8">
                    <h3 class="text-base font-semibold text-[#D4AF37] mb-3">Other Services</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($uncategorized as $service)
                            <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden hover:border-slate-600 transition-colors">
                                <div class="h-1.5 bg-slate-700"></div>
                                <div class="p-4">
                                    <p class="font-semibold text-white">{{ $service->name }}</p>
                                    @if($service->description)
                                        <p class="text-sm text-slate-400 mt-1 line-clamp-2">{{ $service->description }}</p>
                                    @endif
                                    <div class="flex items-center justify-between mt-3">
                                        <span class="font-bold text-white">R{{ number_format($service->price, 2) }}</span>
                                        @if($service->duration_minutes)
                                            <span class="text-xs text-slate-400 bg-slate-800 px-2 py-1 rounded">{{ $service->duration_minutes }}min</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($categories->every(fn($c) => $c->activeServices->isEmpty()) && $uncategorized->isEmpty())
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-10 text-center text-slate-500 text-sm">
                    No active services yet. <a href="{{ route('services.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0]">Add one →</a>
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
