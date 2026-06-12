<x-app-layout>
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-8">
        <div>
            <nav class="flex mb-2">
                <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    <li>Booking</li>
                    <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                    <li class="text-slate-900">Promotions</li>
                </ol>
            </nav>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Promotions</h2>
            <p class="text-slate-400 text-sm mt-1">Time-based discounts applied to services, combos, or everything.</p>
        </div>
        <a href="{{ route('promotions.create') }}"
           class="inline-flex items-center justify-center bg-slate-900 hover:bg-violet-600 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-300 shadow-lg shadow-slate-200 group">
            <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            New Promotion
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    {{-- STATS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach([
            ['label' => 'Total',     'value' => $stats['total'],     'icon' => 'tag'],
            ['label' => 'Live Now',  'value' => $stats['live'],      'icon' => 'bolt'],
            ['label' => 'Scheduled', 'value' => $stats['scheduled'], 'icon' => 'clock'],
            ['label' => 'Expired',   'value' => $stats['expired'],   'icon' => 'x'],
        ] as $stat)
        <div class="bg-white border border-slate-200/60 p-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.02)] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:scale-110 transition-transform">
                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M17.707 9.293a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7A.997.997 0 012 10V5a3 3 0 013-3h5c.256 0 .512.098.707.293l7 7zM5 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"></path></svg>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 mb-1">{{ $stat['label'] }}</p>
            <h3 class="text-3xl font-bold text-slate-900 tabular-nums">{{ $stat['value'] }}</h3>
        </div>
        @endforeach
    </div>

    {{-- TABLE --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Promotion</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Code</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Discount</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Applies To</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Validity Window</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-center">Uses</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($promotions as $promo)
                    @php $statusLabel = $promo->status_label; @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors group">

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-violet-600 transition-colors">{{ $promo->name }}</p>
                                    @if($promo->description)
                                        <p class="text-xs text-slate-400 line-clamp-1">{{ $promo->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            @if($promo->code)
                                <code class="text-xs font-black bg-slate-100 border border-slate-200 px-2 py-1 rounded-lg tracking-widest text-slate-700">{{ $promo->code }}</code>
                            @else
                                <span class="text-slate-300 text-xs">Auto-applied</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <span class="text-sm font-black text-violet-700 bg-violet-50 px-2.5 py-1 rounded-full border border-violet-100">
                                @if($promo->discount_type === 'percentage')
                                    {{ $promo->discount_value }}%
                                @else
                                    R{{ number_format($promo->discount_value, 2) }}
                                @endif
                                <span class="font-normal text-violet-400 text-xs">off</span>
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <span class="text-[11px] font-bold uppercase tracking-tighter px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                {{ $promo->applies_to }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-xs text-slate-500 space-y-0.5">
                                @if($promo->valid_from || $promo->valid_until)
                                    @if($promo->valid_from)
                                        <p><span class="font-bold text-slate-400 uppercase text-[9px] tracking-widest">From</span> {{ $promo->valid_from->format('d M Y, H:i') }}</p>
                                    @endif
                                    @if($promo->valid_until)
                                        <p><span class="font-bold text-slate-400 uppercase text-[9px] tracking-widest">Until</span> {{ $promo->valid_until->format('d M Y, H:i') }}</p>
                                    @endif
                                @else
                                    <span class="text-slate-300">No time limit</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center tabular-nums">
                            @if($promo->max_uses)
                                <div class="text-xs">
                                    <span class="font-bold text-slate-700">{{ $promo->used_count }}</span>
                                    <span class="text-slate-400"> / {{ $promo->max_uses }}</span>
                                </div>
                                <div class="w-16 mx-auto bg-slate-100 rounded-full h-1 mt-1">
                                    <div class="bg-violet-400 h-1 rounded-full" style="width: {{ min(100, ($promo->used_count / $promo->max_uses) * 100) }}%"></div>
                                </div>
                            @else
                                <span class="text-xs text-slate-400">{{ $promo->used_count }} uses</span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center">
                                @php
                                    $badge = match($statusLabel) {
                                        'Live'      => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                        'Scheduled' => 'bg-amber-50 text-amber-600 border-amber-100',
                                        'Expired'   => 'bg-rose-50 text-rose-500 border-rose-100',
                                        'Exhausted' => 'bg-orange-50 text-orange-500 border-orange-100',
                                        default     => 'bg-slate-100 text-slate-400 border-slate-200',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest border {{ $badge }}">
                                    @if($statusLabel === 'Live')<span class="w-1 h-1 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>@endif
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('promotions.edit', $promo) }}" class="text-slate-400 hover:text-slate-900 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>

                                <form method="POST" action="{{ route('promotions.toggle', $promo) }}" class="inline">
                                    @csrf
                                    <button title="{{ $promo->is_active ? 'Deactivate' : 'Activate' }}"
                                            class="text-slate-400 hover:text-violet-500 transition-colors">
                                        @if($promo->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @endif
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('promotions.destroy', $promo) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Delete this promotion?')"
                                            class="text-slate-300 hover:text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-20 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">No promotions yet</p>
                                <a href="{{ route('promotions.create') }}" class="mt-4 text-violet-600 hover:text-violet-700 font-bold text-sm">Create your first promotion →</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 border-t border-slate-100 pt-6">
        {{ $promotions->links() }}
    </div>

</div>
</x-app-layout>
