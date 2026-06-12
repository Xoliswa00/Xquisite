<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">Combined Log View</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Suite Logs</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        <p class="text-sm text-slate-400">
            Last 50 log entries from each app, merged by time.
            Billing entries are live-fetched via the bridge — if billing is offline they won't appear.
        </p>

        <div class="bg-slate-800 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm summary-on-mobile">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Source</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Level</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Message</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Status</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Time</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($all as $log)
                        @php
                            $source = $log['_source'] ?? ($log['source'] ?? 'suite');
                            $level  = strtoupper($log['level'] ?? '');
                            $status = $log['status'] ?? 'new';
                        @endphp
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-bold
                                    {{ $source === 'billing' ? 'bg-purple-900/60 text-purple-300' : 'bg-slate-700 text-slate-300' }}">
                                    {{ $source }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-bold
                                    @if(in_array($level, ['ERROR','CRITICAL','ALERT','EMERGENCY'])) bg-red-900/60 text-red-300
                                    @elseif($level === 'WARNING') bg-yellow-900/60 text-yellow-300
                                    @elseif($level === 'INFO') bg-blue-900/60 text-blue-300
                                    @else bg-slate-700 text-slate-400 @endif">
                                    {{ $level }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-200 max-w-xs truncate">{{ $log['message'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($status === 'new') bg-red-900/40 text-red-300
                                    @elseif($status === 'acknowledged') bg-yellow-900/40 text-yellow-300
                                    @elseif($status === 'in_progress') bg-blue-900/40 text-blue-300
                                    @else bg-green-900/40 text-green-300 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs whitespace-nowrap">
                                {{ isset($log['created_at']) ? \Carbon\Carbon::parse($log['created_at'])->format('d M H:i') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($source === 'suite' && isset($log['id']))
                                    <a href="{{ route('admin.logs.show', $log['id']) }}"
                                       class="text-indigo-400 hover:text-indigo-300 text-xs">View</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">No logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
