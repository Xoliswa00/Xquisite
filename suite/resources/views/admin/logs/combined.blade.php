<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4 flex-wrap">
            <h2 class="text-xl font-bold text-[#D4AF37]">Combined Log View</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Suite Logs</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-5">

        <p class="text-sm text-slate-400">
            Last 50 entries from each app, merged by time. Billing entries are live-fetched — if billing is offline they won't appear.
        </p>

        {{-- ── DESKTOP TABLE ───────────────────────────────────────────── --}}
        <div class="hidden sm:block bg-slate-800 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm">
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
                            $lc = in_array($level, ['ERROR','CRITICAL','ALERT','EMERGENCY'])
                                ? 'bg-red-900/60 text-red-300'
                                : ($level === 'WARNING' ? 'bg-yellow-900/60 text-yellow-300'
                                : ($level === 'INFO'    ? 'bg-blue-900/60 text-blue-300'
                                : 'bg-slate-700 text-slate-400'));
                            $sc = match($status) {
                                'new'         => 'bg-red-900/40 text-red-300',
                                'acknowledged'=> 'bg-yellow-900/40 text-yellow-300',
                                'in_progress' => 'bg-blue-900/40 text-blue-300',
                                default       => 'bg-green-900/40 text-green-300',
                            };
                        @endphp
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-bold
                                    {{ $source === 'billing' ? 'bg-[#0078D4]/20 text-[#B8D4F0]' : 'bg-slate-700 text-slate-300' }}">
                                    {{ $source }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $lc }}">{{ $level }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-200 max-w-xs truncate">{{ $log['message'] ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs {{ $sc }}">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs whitespace-nowrap">
                                {{ isset($log['created_at']) ? \Carbon\Carbon::parse($log['created_at'])->format('d M H:i') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if($source === 'suite' && isset($log['id']))
                                    <a href="{{ route('admin.logs.show', $log['id']) }}"
                                       class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
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

        {{-- ── MOBILE CARDS ────────────────────────────────────────────── --}}
        <div class="block sm:hidden space-y-2">
            @forelse($all as $log)
                @php
                    $source = $log['_source'] ?? ($log['source'] ?? 'suite');
                    $level  = strtoupper($log['level'] ?? '');
                    $status = $log['status'] ?? 'new';
                    $lc = in_array($level, ['ERROR','CRITICAL','ALERT','EMERGENCY'])
                        ? 'bg-red-900/60 text-red-300'
                        : ($level === 'WARNING' ? 'bg-yellow-900/60 text-yellow-300'
                        : ($level === 'INFO'    ? 'bg-blue-900/60 text-blue-300'
                        : 'bg-slate-700 text-slate-400'));
                    $sc = match($status) {
                        'new'         => 'bg-red-900/40 text-red-300',
                        'acknowledged'=> 'bg-yellow-900/40 text-yellow-300',
                        'in_progress' => 'bg-blue-900/40 text-blue-300',
                        default       => 'bg-green-900/40 text-green-300',
                    };
                @endphp
                <div class="bg-slate-800 rounded-xl px-4 py-3 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2 py-0.5 rounded text-xs font-bold
                                {{ $source === 'billing' ? 'bg-[#0078D4]/20 text-[#B8D4F0]' : 'bg-slate-700 text-slate-300' }}">
                                {{ $source }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-xs font-bold {{ $lc }}">{{ $level }}</span>
                            <span class="px-2 py-0.5 rounded text-xs {{ $sc }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </div>
                        <span class="text-slate-500 text-xs whitespace-nowrap shrink-0">
                            {{ isset($log['created_at']) ? \Carbon\Carbon::parse($log['created_at'])->format('d M H:i') : '—' }}
                        </span>
                    </div>
                    <p class="text-slate-200 text-sm leading-snug line-clamp-2">{{ $log['message'] ?? '—' }}</p>
                    @if($source === 'suite' && isset($log['id']))
                        <div class="flex justify-end">
                            <a href="{{ route('admin.logs.show', $log['id']) }}"
                               class="text-[#0078D4] hover:text-[#B8D4F0] text-xs font-medium">View &rarr;</a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-slate-800 rounded-xl px-4 py-10 text-center text-slate-500 text-sm">
                    No logs found.
                </div>
            @endforelse
        </div>

    </div>
</x-app-layout>
