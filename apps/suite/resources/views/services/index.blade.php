<x-app-layout>
    <x-slot name="header">Services</x-slot>

    <div class="space-y-4" x-data="{ tab: '{{ $tab }}' }">

        {{-- ── Tab bar + create buttons ─────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">

            {{-- Tabs --}}
            <div class="flex gap-1 bg-slate-800 p-1 rounded-xl overflow-x-auto">
                <button @click="tab='services'" type="button"
                        :class="tab==='services' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    Services
                    <span class="ml-1.5 text-xs opacity-70">{{ $services->total() }}</span>
                </button>
                <button @click="tab='combos'" type="button"
                        :class="tab==='combos' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    Combos
                    <span class="ml-1.5 text-xs opacity-70">{{ $combos->total() }}</span>
                </button>
                <button @click="tab='promotions'" type="button"
                        :class="tab==='promotions' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'"
                        class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors">
                    Promotions
                    <span class="ml-1.5 text-xs opacity-70">{{ $promotions->total() }}</span>
                </button>
            </div>

            {{-- Context-aware create button --}}
            <div class="flex gap-2">
                <a x-show="tab==='services'" href="{{ route('services.create') }}"
                   class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                    + New Service
                </a>
                <a x-show="tab==='combos'" href="{{ route('combos.create') }}"
                   class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                    + New Combo
                </a>
                <a x-show="tab==='promotions'" href="{{ route('promotions.create') }}"
                   class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                    + New Promotion
                </a>
            </div>
        </div>

        {{-- ── Services tab ─────────────────────────────────────────────────── --}}
        <div x-show="tab==='services'" x-cloak>
            <form method="GET" class="flex gap-2 mb-3">
                <input type="hidden" name="tab" value="services">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search services…"
                       class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 flex-1 sm:flex-none sm:w-48 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg text-slate-200">Search</button>
                @if(request('search'))
                    <a href="{{ route('services.index') }}" class="text-sm px-4 py-2 rounded-lg text-slate-400 hover:text-white">Clear</a>
                @endif
            </form>

            {{-- Mobile cards --}}
            <div class="sm:hidden space-y-2">
                @forelse($services as $service)
                    <a href="{{ route('services.edit', $service) }}"
                       class="block bg-slate-800 rounded-xl p-4 hover:bg-slate-700/70 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-medium text-white text-sm">{{ $service->name }}</p>
                                @if($service->description)
                                    <p class="text-xs text-slate-500 mt-0.5 truncate">{{ Str::limit($service->description, 60) }}</p>
                                @endif
                            </div>
                            @if($service->is_active)
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                            @else
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                            <span>{{ $service->duration_minutes }} min</span>
                            <span>·</span>
                            <span>R{{ number_format($service->price, 2) }}</span>
                            @if($service->category)
                                <span>·</span>
                                <span>{{ $service->category->icon }} {{ $service->category->name }}</span>
                            @endif
                        </div>
                    </a>
                @empty
                    <p class="text-center text-slate-500 py-10 text-sm">
                        No services yet. <a href="{{ route('services.create') }}" class="text-indigo-400">Add one.</a>
                    </p>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <div class="hidden sm:block bg-slate-800 rounded-xl overflow-hidden overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 text-slate-400 text-left">
                            <th class="px-4 py-3 font-medium">Name</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Duration</th>
                            <th class="px-4 py-3 font-medium">Price</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700">
                        @forelse($services as $service)
                            <tr class="hover:bg-slate-700/50">
                                <td class="px-4 py-3">
                                    <p class="text-white font-medium">{{ $service->name }}</p>
                                    @if($service->description)
                                        <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($service->description, 55) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($service->category)
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                            {{ $service->category->colorClass('bg') }} {{ $service->category->colorClass('text') }}">
                                            {{ $service->category->icon }} {{ $service->category->name }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-600">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-300">{{ $service->duration_minutes }} min</td>
                                <td class="px-4 py-3 text-slate-300">R{{ number_format($service->price, 2) }}</td>
                                <td class="px-4 py-3">
                                    @if($service->is_active)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('services.edit', $service) }}" class="text-slate-400 hover:text-white text-xs">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                                    No services yet. <a href="{{ route('services.create') }}" class="text-indigo-400 hover:text-indigo-300">Add one.</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $services->appends(['tab' => 'services', 'search' => request('search')])->links() }}</div>
        </div>

        {{-- ── Combos tab ────────────────────────────────────────────────────── --}}
        <div x-show="tab==='combos'" x-cloak>
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                @forelse($combos as $combo)
                    @php
                        $status = $combo->status_label;
                        $badgeClass = match($status) {
                            'Live'      => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'Scheduled' => 'bg-sky-100 text-sky-700 border-sky-200',
                            'Expired'   => 'bg-slate-100 text-slate-500 border-slate-200',
                            default     => 'bg-red-100 text-red-700 border-red-200',
                        };
                    @endphp
                    <div class="px-5 py-4 border-b border-slate-700 last:border-0 hover:bg-slate-700/30">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-medium text-white">{{ $combo->name }}</p>
                                    <span class="text-xs px-2 py-0.5 rounded-full border font-medium {{ $badgeClass }}">{{ $status }}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5 truncate">{{ $combo->services->pluck('name')->join(', ') }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-xs text-slate-500 line-through">R{{ number_format($combo->total_service_price, 2) }}</p>
                                <p class="font-bold text-white text-sm">R{{ number_format($combo->combo_price, 2) }}</p>
                                <p class="text-xs text-emerald-400">Save R{{ number_format($combo->savings, 2) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3 flex-wrap">
                            <form method="POST" action="{{ route('combos.toggle', $combo) }}">
                                @csrf
                                <button class="text-xs px-3 py-1.5 rounded-lg border {{ $combo->is_active ? 'border-slate-600 text-slate-300 hover:bg-slate-700' : 'border-indigo-700 text-indigo-400 hover:bg-indigo-900/40' }}">
                                    {{ $combo->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <a href="{{ route('combos.edit', $combo) }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700">Edit</a>
                            <form method="POST" action="{{ route('combos.destroy', $combo) }}" onsubmit="return confirm('Delete this combo?')">
                                @csrf @method('DELETE')
                                <button class="text-xs px-3 py-1.5 rounded-lg border border-red-800 text-red-400 hover:bg-red-900/30">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-slate-500 text-sm">
                        No combos yet. <a href="{{ route('combos.create') }}" class="text-indigo-400 hover:text-indigo-300">Create one.</a>
                    </div>
                @endforelse
            </div>
            <div class="mt-3">{{ $combos->appends(['tab' => 'combos'])->links() }}</div>
        </div>

        {{-- ── Promotions tab ────────────────────────────────────────────────── --}}
        <div x-show="tab==='promotions'" x-cloak>
            <div class="bg-slate-800 rounded-xl overflow-hidden">
                @forelse($promotions as $promo)
                    @php
                        $status = $promo->status_label;
                        $badgeClass = match($status) {
                            'Live'      => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'Scheduled' => 'bg-sky-100 text-sky-700 border-sky-200',
                            'Expired'   => 'bg-slate-100 text-slate-500 border-slate-200',
                            'Exhausted' => 'bg-orange-100 text-orange-700 border-orange-200',
                            default     => 'bg-red-100 text-red-700 border-red-200',
                        };
                    @endphp
                    <div class="px-5 py-4 border-b border-slate-700 last:border-0 hover:bg-slate-700/30">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-medium text-white">{{ $promo->name }}</p>
                                    <code class="text-xs bg-slate-700 px-2 py-0.5 rounded text-amber-300">{{ $promo->code }}</code>
                                    <span class="text-xs px-2 py-0.5 rounded-full border font-medium {{ $badgeClass }}">{{ $status }}</span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    {{ $promo->discount_type === 'percentage' ? $promo->discount_value . '%' : 'R' . number_format($promo->discount_value, 2) }} off
                                    · {{ $promo->applies_to }}
                                </p>
                            </div>
                            @if($promo->max_uses)
                                @php $pct = min(100, round(($promo->used_count / $promo->max_uses) * 100)); @endphp
                                <div class="shrink-0 w-20 text-right">
                                    <p class="text-xs text-slate-500 mb-1">{{ $promo->used_count }}/{{ $promo->max_uses }}</p>
                                    <div class="h-1.5 bg-slate-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $pct }}%"></div>
                                    </div>
                                </div>
                            @else
                                <p class="shrink-0 text-xs text-slate-500">{{ $promo->used_count }} uses</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mt-3 flex-wrap">
                            <form method="POST" action="{{ route('promotions.toggle', $promo) }}">
                                @csrf
                                <button class="text-xs px-3 py-1.5 rounded-lg border {{ $promo->is_active ? 'border-slate-600 text-slate-300 hover:bg-slate-700' : 'border-indigo-700 text-indigo-400 hover:bg-indigo-900/40' }}">
                                    {{ $promo->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <a href="{{ route('promotions.edit', $promo) }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-700">Edit</a>
                            <form method="POST" action="{{ route('promotions.destroy', $promo) }}" onsubmit="return confirm('Delete this promotion?')">
                                @csrf @method('DELETE')
                                <button class="text-xs px-3 py-1.5 rounded-lg border border-red-800 text-red-400 hover:bg-red-900/30">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-slate-500 text-sm">
                        No promotions yet. <a href="{{ route('promotions.create') }}" class="text-indigo-400 hover:text-indigo-300">Create one.</a>
                    </div>
                @endforelse
            </div>
            <div class="mt-3">{{ $promotions->appends(['tab' => 'promotions'])->links() }}</div>
        </div>

    </div>
</x-app-layout>
