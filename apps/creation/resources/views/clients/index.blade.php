<x-app-layout>
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-1">Entity Management</p>
                <h2 class="font-black text-2xl text-slate-900 tracking-tighter leading-tight">
                </h2>
            </div>

            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl hover:bg-indigo-600 transition-all shadow-xl shadow-slate-900/10 active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                Provision Client
            </a>
        </div>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- HIGH-VISIBILITY METRICS --}}
            @php
                $stats = [
                    ['label' => 'Total Registry', 'value' => $clients->count(), 'sub' => 'Active Entities', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857...'],
                    ['label' => 'Portal Access', 'value' => $clients->whereNotNull('user_id')->count(), 'sub' => 'Authenticated', 'color' => 'text-indigo-600'],
                    ['label' => 'Growth Index', 'value' => $clients->where('created_at', '>=', now()->startOfMonth())->count(), 'sub' => 'New This Month', 'color' => 'text-emerald-600'],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($stats as $stat)
                    <div class="bg-white border border-slate-200/60 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">{{ $stat['label'] }}</p>
                        <div class="flex items-end justify-between mt-3">
                            <div>
                                <p class="text-4xl font-black {{ $stat['color'] ?? 'text-slate-900' }} tracking-tighter">
                                    {{ sprintf('%02d', $stat['value']) }}
                                </p>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">{{ $stat['sub'] }}</p>
                            </div>
                            <div class="h-12 w-12 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- SEARCH & UTILITIES (Mockup) --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                    <input type="text" placeholder="FILTER REGISTRY..." 
                           class="w-full pl-11 pr-4 py-3 bg-white border-slate-200 rounded-2xl text-[10px] font-bold uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all">
                </div>
            </div>

            {{-- DATA GRID --}}
            <div class="bg-white border border-slate-200/60 rounded-3xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Client Identity</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Communication Channel</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Security Status</th>
                                <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Control</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-50">
                            @forelse($clients as $client)
                                <tr class="hover:bg-slate-50/50 transition-colors group">
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="flex items-center gap-4">
                                            <div class="h-12 w-12 flex-shrink-0 flex items-center justify-center rounded-2xl bg-slate-900 text-white font-black text-xs shadow-lg shadow-slate-900/20">
                                                {{ strtoupper(substr($client->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="text-sm font-black text-slate-900 tracking-tight">{{ $client->name }}</div>
                                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">UID: {{ str_pad($client->id, 5, '0', STR_PAD_LEFT) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        <div class="text-xs font-bold text-slate-700">{{ $client->email ?? 'N/A' }}</div>
                                        <div class="text-[10px] font-medium text-slate-400 mt-1">{{ $client->phone ?? 'NO TELEPHONY' }}</div>
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap">
                                        @if($client->user_id)
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                Portal Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-400 border border-slate-200">
                                                Off-Platform
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5 whitespace-nowrap text-right">
                                        <div class="flex justify-end items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('clients.edit', $client) }}" 
                                               class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:shadow-sm rounded-xl transition-all">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Confirm Deletion?')">
                                                @csrf @method('DELETE')
                                                <button class="p-2.5 bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:shadow-sm rounded-xl transition-all">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-24 text-center">
                                        <div class="flex flex-col items-center">
                                            <div class="h-20 w-20 bg-slate-50 rounded-full flex items-center justify-center mb-6 ring-12 ring-slate-50/50">
                                                <svg class="h-10 w-10 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                                    <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-900">Registry Empty</h3>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">No client records found in the database.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>