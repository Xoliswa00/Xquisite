<x-app-layout>
    <x-slot name="header">Instance Monitoring</x-slot>

    <div class="space-y-6">
        {{-- Header with Add Instance button --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-white">Monitored Instances</h2>
                <p class="text-slate-400 text-sm mt-1">Track health and status of all remote app instances</p>
            </div>
            <a href="{{ route('monitoring.create') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Instance
            </a>
        </div>

        {{-- Stats Overview --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm">Total Instances</p>
                        <p class="text-2xl font-bold text-white mt-1">{{ $instances->count() }}</p>
                    </div>
                    <svg class="w-8 h-8 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm">Healthy</p>
                        <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $instances->filter(fn($i) => $i->is_healthy)->count() }}</p>
                    </div>
                    <svg class="w-8 h-8 text-emerald-600/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm">Down/Unhealthy</p>
                        <p class="text-2xl font-bold {{ $instances->filter(fn($i) => !$i->is_healthy)->count() > 0 ? 'text-red-400' : 'text-slate-400' }} mt-1">
                            {{ $instances->filter(fn($i) => !$i->is_healthy)->count() }}
                        </p>
                    </div>
                    <svg class="w-8 h-8 text-red-600/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-400 text-sm">Avg Uptime</p>
                        <p class="text-2xl font-bold text-indigo-400 mt-1">
                            @if($instances->count() > 0)
                                {{ number_format($instances->average('uptime_percentage') ?? 0, 1) }}%
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <svg class="w-8 h-8 text-indigo-600/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
            </div>
        </div>

        {{-- Instances List --}}
        @if($instances->count() > 0)
            <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm summary-on-mobile">
                        <thead class="bg-slate-900/50 border-b border-slate-700">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Instance</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Tenant</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Status</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Uptime</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Last Check</th>
                                <th class="px-6 py-3 text-left font-semibold text-slate-300">Response Time</th>
                                <th class="px-6 py-3 text-right font-semibold text-slate-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700">
                            @foreach($instances as $instance)
                                <tr class="hover:bg-slate-700/50 transition">
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="font-medium text-white">{{ $instance->name }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $instance->url }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-slate-300">{{ $instance->tenant_id ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full {{ $instance->is_healthy ? 'bg-emerald-500' : 'bg-red-500' }} animate-pulse"></span>
                                            <span class="text-sm {{ $instance->is_healthy ? 'text-emerald-400 font-medium' : 'text-red-400 font-medium' }}">
                                                {{ $instance->is_healthy ? 'Healthy' : 'Unhealthy' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-mono">
                                            @if($instance->uptime_percentage !== null)
                                                {{ number_format($instance->uptime_percentage, 1) }}%
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs text-slate-400">
                                            @if($instance->lastHealthCheck)
                                                {{ $instance->lastHealthCheck->created_at->diffForHumans() }}
                                            @else
                                                Never
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm font-mono text-slate-400">
                                            @if($instance->lastHealthCheck?->response_time_ms)
                                                {{ $instance->lastHealthCheck->response_time_ms }}ms
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('monitoring.show', $instance) }}"
                                               class="inline-flex items-center px-3 py-1.5 text-xs bg-slate-700 hover:bg-slate-600 text-slate-300 rounded transition">
                                                View
                                            </a>
                                            <button type="button"
                                                    onclick="if(confirm('Delete this instance?')) { document.getElementById('delete-form-{{ $instance->id }}').submit(); }"
                                                    class="inline-flex items-center px-3 py-1.5 text-xs bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded transition">
                                                Delete
                                            </button>
                                            <form id="delete-form-{{ $instance->id }}" 
                                                  action="{{ route('monitoring.destroy', $instance) }}" 
                                                  method="POST" class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-slate-800 rounded-xl p-12 border border-slate-700 text-center">
                <svg class="w-12 h-12 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                <h3 class="text-lg font-semibold text-slate-300">No instances yet</h3>
                <p class="text-slate-400 text-sm mt-1 mb-4">Start monitoring remote app instances by adding one.</p>
                <a href="{{ route('monitoring.create') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Your First Instance
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
