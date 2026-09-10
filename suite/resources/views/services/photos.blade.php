<x-app-layout>
    <x-slot name="header">Service Photos</x-slot>

    @php
        $maxPhotos = \App\Modules\Booking\Models\Service::MAX_PHOTOS;

        // Group the current page by category for the "Grouped" view — Uncategorised last.
        $grouped = $services->getCollection()
            ->groupBy(fn($s) => $s->category?->name ?? 'Uncategorised')
            ->sortKeys()
            ->sortBy(fn($group, $key) => $key === 'Uncategorised' ? 1 : 0);
    @endphp

    <div class="space-y-4"
         x-data="{
            view: 'list',
            init() {
                try { this.view = localStorage.getItem('svcPhotosView') || 'list'; } catch (e) {}
            },
            set(v) {
                this.view = v;
                try { localStorage.setItem('svcPhotosView', v); } catch (e) {}
            }
         }">

        {{-- Intro + back --}}
        <div class="flex flex-col sm:flex-row gap-2 sm:items-center justify-between">
            <p class="text-sm text-slate-400">Add or change the photos customers see on your public booking page.</p>
            <a href="{{ route('services.index') }}"
               class="text-sm text-slate-400 hover:text-white whitespace-nowrap">← Back to services</a>
        </div>

        {{-- Search + status filters --}}
        <form method="GET" class="flex flex-wrap gap-2 items-center">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search services…"
                   class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 flex-1 min-w-0 sm:w-64 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg text-slate-200">Search</button>
            @if(request()->hasAny(['search', 'filter']))
                <a href="{{ route('services.photos.index') }}" class="text-sm px-3 py-2 rounded-lg text-slate-400 hover:text-white">Clear</a>
            @endif

            <div class="w-full sm:w-auto sm:ml-auto flex gap-1.5">
                @foreach ([
                    null      => ['All', $counts['all']],
                    'missing' => ['Missing photos', $counts['missing']],
                    'has'     => ['Has photos', $counts['has']],
                ] as $key => [$label, $count])
                    <a href="{{ route('services.photos.index', array_filter(['filter' => $key, 'search' => request('search')])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors whitespace-nowrap
                              {{ $filter === $key ? 'bg-[#0078D4] text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                        {{ $label }} <span class="opacity-70">{{ $count }}</span>
                    </a>
                @endforeach
            </div>
        </form>

        {{-- View switcher --}}
        <div class="flex items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                {{ $services->total() }} {{ Str::plural('service', $services->total()) }}
            </p>
            <div class="inline-flex rounded-lg border border-slate-700 bg-slate-800 p-0.5">
                <button type="button" @click="set('list')"
                        :class="view === 'list' ? 'bg-[#0078D4] text-white' : 'text-slate-400 hover:text-white'"
                        class="px-2.5 py-1.5 rounded-md text-xs font-medium flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span class="hidden sm:inline">List</span>
                </button>
                <button type="button" @click="set('grid')"
                        :class="view === 'grid' ? 'bg-[#0078D4] text-white' : 'text-slate-400 hover:text-white'"
                        class="px-2.5 py-1.5 rounded-md text-xs font-medium flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h6v6H4zM14 5h6v6h-6zM4 15h6v4H4zM14 13h6v6h-6z"/></svg>
                    <span class="hidden sm:inline">Gallery</span>
                </button>
                <button type="button" @click="set('grouped')"
                        :class="view === 'grouped' ? 'bg-[#0078D4] text-white' : 'text-slate-400 hover:text-white'"
                        class="px-2.5 py-1.5 rounded-md text-xs font-medium flex items-center gap-1.5 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7l2-2h5l2 2h9v12H3zM3 7v12"/></svg>
                    <span class="hidden sm:inline">By category</span>
                </button>
            </div>
        </div>

        @if($services->isEmpty())
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-10 text-center text-sm text-slate-400">
                @if(request('search'))
                    No services match “{{ request('search') }}”.
                @elseif($filter === 'missing')
                    Every service has at least one photo. 🎉
                @else
                    No services yet. <a href="{{ route('services.create') }}" class="text-[#0078D4]">Add one.</a>
                @endif
            </div>
        @else

            {{-- ── List ── --}}
            <div x-show="view === 'list'" x-cloak
                 class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
                @foreach($services as $service)
                    @include('services.partials.photo-row', ['service' => $service, 'maxPhotos' => $maxPhotos])
                @endforeach
            </div>

            {{-- ── Gallery / grid ── --}}
            <div x-show="view === 'grid'" x-cloak
                 class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($services as $service)
                    @include('services.partials.photo-card', ['service' => $service, 'maxPhotos' => $maxPhotos])
                @endforeach
            </div>

            {{-- ── By category ── --}}
            <div x-show="view === 'grouped'" x-cloak class="space-y-5">
                @foreach($grouped as $categoryName => $group)
                    <div x-data="{ open: true }">
                        <button type="button" @click="open = !open"
                                class="flex items-center gap-2 w-full text-left mb-1.5">
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $categoryName }}</span>
                            <span class="text-[11px] text-slate-600">({{ $group->count() }})</span>
                            <svg class="w-3.5 h-3.5 text-slate-600 ml-auto transition-transform duration-150" :class="open ? '' : '-rotate-90'"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-cloak
                             class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700 overflow-hidden">
                            @foreach($group as $service)
                                @include('services.partials.photo-row', ['service' => $service, 'maxPhotos' => $maxPhotos, 'hideCategory' => true])
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $services->links() }}</div>
        @endif
    </div>
</x-app-layout>
