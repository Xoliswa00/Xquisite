<x-app-layout>
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-8">
        <div>
            <nav class="flex mb-2">
                <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    <li>Booking</li>
                    <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                    <li class="text-slate-900">Service Combos</li>
                </ol>
            </nav>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Service Combos</h2>
            <p class="text-slate-400 text-sm mt-1">Bundle services together with a discounted combo price.</p>
        </div>
        <a href="{{ route('combos.create') }}"
           class="inline-flex items-center justify-center bg-slate-900 hover:bg-amber-400 hover:text-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-300 shadow-lg shadow-slate-200 group">
            <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            New Combo
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
            ['label' => 'Total Combos',   'value' => $stats['total'],   'color' => 'slate'],
            ['label' => 'Active',          'value' => $stats['active'],  'color' => 'emerald'],
            ['label' => 'Live Now',        'value' => $stats['live'],    'color' => 'indigo'],
            ['label' => 'Expired',         'value' => $stats['expired'], 'color' => 'rose'],
        ] as $stat)
        <div class="bg-white border border-slate-200/60 p-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.02)] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:scale-110 transition-transform">
                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z"></path></svg>
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
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Combo Name</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Services</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Full Price</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Discount</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Combo Price</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Validity</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($combos as $combo)
                    @php
                        $total   = $combo->total_service_price;
                        $price   = $combo->combo_price;
                        $savings = $combo->savings;
                        $live    = $combo->isLive();
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition-colors group">

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-900 group-hover:text-amber-600 transition-colors">{{ $combo->name }}</p>
                                    @if($combo->description)
                                        <p class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $combo->description }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @foreach($combo->services->take(3) as $service)
                                    <span class="text-[10px] font-bold uppercase tracking-tighter px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $service->name }}
                                    </span>
                                @endforeach
                                @if($combo->services->count() > 3)
                                    <span class="text-[10px] font-bold text-slate-400">+{{ $combo->services->count() - 3 }} more</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 text-right tabular-nums">
                            <span class="text-sm text-slate-400 line-through">R{{ number_format($total, 2) }}</span>
                        </td>

                        <td class="px-6 py-4 text-right tabular-nums">
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                @if($combo->discount_type === 'percentage')
                                    {{ $combo->discount_value }}% off
                                @else
                                    R{{ number_format($combo->discount_value, 2) }} off
                                @endif
                            </span>
                        </td>

                        <td class="px-6 py-4 text-right tabular-nums">
                            <div>
                                <span class="text-sm font-black text-slate-900">R{{ number_format($price, 2) }}</span>
                                <p class="text-[10px] text-emerald-600 font-bold">save R{{ number_format($savings, 2) }}</p>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-xs text-slate-500 space-y-0.5">
                                @if($combo->valid_from || $combo->valid_until)
                                    @if($combo->valid_from)
                                        <p><span class="font-bold text-slate-400 uppercase text-[9px] tracking-widest">From</span> {{ $combo->valid_from->format('d M Y') }}</p>
                                    @endif
                                    @if($combo->valid_until)
                                        <p><span class="font-bold text-slate-400 uppercase text-[9px] tracking-widest">Until</span> {{ $combo->valid_until->format('d M Y') }}</p>
                                    @endif
                                @else
                                    <span class="text-slate-300 text-xs">No limit</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center">
                                @if($live)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <span class="w-1 h-1 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Live
                                    </span>
                                @elseif(!$combo->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-400 border border-slate-200">
                                        Inactive
                                    </span>
                                @elseif($combo->valid_until && now()->gt($combo->valid_until))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-rose-50 text-rose-500 border border-rose-100">
                                        Expired
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-600 border border-amber-100">
                                        Scheduled
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('combos.edit', $combo) }}" class="text-slate-400 hover:text-slate-900 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>

                                <form method="POST" action="{{ route('combos.toggle', $combo) }}" class="inline">
                                    @csrf
                                    <button title="{{ $combo->is_active ? 'Deactivate' : 'Activate' }}"
                                            class="text-slate-400 hover:text-amber-500 transition-colors">
                                        @if($combo->is_active)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @endif
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('combos.destroy', $combo) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Delete this combo?')" title="Delete"
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
                                <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">No combos yet</p>
                                <a href="{{ route('combos.create') }}" class="mt-4 text-amber-600 hover:text-amber-700 font-bold text-sm">Create your first combo →</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 border-t border-slate-100 pt-6">
        {{ $combos->links() }}
    </div>

</div>
</x-app-layout>
