<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-[#D4AF37]">Service Photos</h2>
    </x-slot>

    <div class="space-y-6">

        {{-- Filter tabs --}}
        <div class="flex flex-wrap items-center gap-2">
            @foreach ([
                'reported' => 'Reported' . ($openReports > 0 ? " ({$openReports})" : ''),
                'hidden'   => 'Hidden',
                'all'      => 'All',
            ] as $key => $label)
                <a href="{{ route('admin.service-photos.index', ['filter' => $key]) }}"
                   class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                          {{ $filter === $key ? 'bg-[#0078D4] text-white' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($photos->isEmpty())
            <div class="bg-slate-800 rounded-xl border border-slate-700 p-10 text-center text-sm text-slate-400">
                @if ($filter === 'reported')
                    Nothing reported. Clean slate.
                @elseif ($filter === 'hidden')
                    No photos are currently hidden.
                @else
                    No service photos yet.
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($photos as $photo)
                    <div class="bg-slate-800 rounded-xl border {{ $photo->open_reports_count > 0 ? 'border-amber-600/60' : 'border-slate-700' }} overflow-hidden flex flex-col">
                        <div class="relative">
                            <img src="{{ $photo->thumbUrl() }}" alt="" loading="lazy"
                                 width="600" height="400"
                                 class="w-full aspect-[3/2] object-cover bg-slate-900 {{ $photo->isHidden() ? 'opacity-40' : '' }}">
                            @if ($photo->is_primary)
                                <span class="absolute top-2 left-2 bg-[#0078D4] text-white text-[10px] font-bold px-2 py-0.5 rounded">Cover</span>
                            @endif
                            @if ($photo->isHidden())
                                <span class="absolute top-2 right-2 bg-red-900/90 text-red-100 text-[10px] font-semibold px-2 py-0.5 rounded">Hidden</span>
                            @endif
                            @if ($photo->open_reports_count > 0)
                                <span class="absolute bottom-2 left-2 bg-amber-500 text-amber-950 text-[10px] font-bold px-2 py-0.5 rounded">
                                    {{ $photo->open_reports_count }} open {{ Str::plural('report', $photo->open_reports_count) }}
                                </span>
                            @endif
                        </div>

                        <div class="p-4 flex-1 flex flex-col gap-2">
                            <div class="text-sm">
                                <p class="font-semibold text-slate-200 truncate">{{ $photo->service?->name ?? 'Deleted service' }}</p>
                                <p class="text-xs text-slate-500 truncate">
                                    {{ $photo->service?->tenant?->name ?? '—' }}
                                    @if ($photo->service?->tenant?->slug)
                                        · <a href="{{ url('book/' . $photo->service->tenant->slug) }}" target="_blank" rel="noopener"
                                             class="text-[#0078D4] hover:underline">view booking page ↗</a>
                                    @endif
                                </p>
                            </div>

                            <p class="text-[11px] text-slate-500">
                                Uploaded {{ $photo->created_at?->diffForHumans() }} ·
                                {{ $photo->reports_count }} total {{ Str::plural('report', $photo->reports_count) }}
                            </p>

                            <div class="mt-auto flex items-center gap-2 pt-2">
                                <form method="POST" action="{{ route('admin.service-photos.toggle-hidden', $photo) }}">
                                    @csrf @method('PATCH')
                                    @if ($photo->isHidden())
                                        <button class="bg-slate-700 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors">
                                            Restore
                                        </button>
                                    @else
                                        <button class="bg-red-800 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors"
                                                onclick="return confirm('Hide this photo from the public booking page?')">
                                            Hide
                                        </button>
                                    @endif
                                </form>
                                <a href="{{ $photo->url() }}" target="_blank" rel="noopener"
                                   class="text-xs text-slate-400 hover:text-white px-2 py-1.5">Full size ↗</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div>{{ $photos->links() }}</div>
        @endif
    </div>
</x-app-layout>
