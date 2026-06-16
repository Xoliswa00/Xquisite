<x-app-layout>
    <x-slot name="header">System Logs</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-xl font-bold text-white">System Logs</h1>
                @if($unresolved > 0)
                    <p class="text-sm text-red-400 mt-0.5">{{ $unresolved }} unresolved critical error{{ $unresolved > 1 ? 's' : '' }}</p>
                @endif
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.logs.audit') }}"
                   class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm rounded-lg transition-colors">
                    Audit Trail
                </a>
                <a href="{{ route('admin.logs.combined') }}"
                   class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg transition-colors">
                    Combined View
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-900/30 border border-green-700 text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="level" class="bg-slate-800 border-slate-600 text-slate-300 rounded-lg text-sm">
                <option value="">All Levels</option>
                @foreach(['DEBUG','INFO','WARNING','ERROR','CRITICAL','ALERT','EMERGENCY'] as $lvl)
                    <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                @endforeach
            </select>

            <select name="status" class="bg-slate-800 border-slate-600 text-slate-300 rounded-lg text-sm">
                <option value="">All Statuses</option>
                @foreach(['new','acknowledged','in_progress','resolved'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>

            <select name="source" class="bg-slate-800 border-slate-600 text-slate-300 rounded-lg text-sm">
                <option value="">All Sources</option>
                <option value="suite" {{ request('source') === 'suite' ? 'selected' : '' }}>Suite</option>
                <option value="billing" {{ request('source') === 'billing' ? 'selected' : '' }}>Billing</option>
            </select>

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search message…"
                   class="bg-slate-800 border-slate-600 text-slate-300 rounded-lg text-sm flex-1 min-w-48">

            <button type="submit" class="px-4 py-2 bg-slate-700 text-white rounded-lg text-sm hover:bg-slate-600">Filter</button>
            <a href="{{ route('admin.logs.index') }}" class="px-4 py-2 text-slate-400 rounded-lg text-sm hover:text-white">Clear</a>
        </form>

        {{-- Table --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm summary-on-mobile">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Level</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Message</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Source</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Status</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Time</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-bold
                                    @if(in_array($log->level, ['ERROR','CRITICAL','ALERT','EMERGENCY'])) bg-red-900/60 text-red-300
                                    @elseif($log->level === 'WARNING') bg-yellow-900/60 text-yellow-300
                                    @elseif($log->level === 'INFO') bg-blue-900/60 text-blue-300
                                    @else bg-slate-700 text-slate-400 @endif">
                                    {{ $log->level }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-200 max-w-xs truncate">{{ $log->message }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $log->source }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($log->status === 'new') bg-red-900/40 text-red-300
                                    @elseif($log->status === 'acknowledged') bg-yellow-900/40 text-yellow-300
                                    @elseif($log->status === 'in_progress') bg-blue-900/40 text-blue-300
                                    @else bg-green-900/40 text-green-300 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs whitespace-nowrap">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.logs.show', $log) }}"
                                   class="text-indigo-400 hover:text-indigo-300 text-xs">View</a>
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

        <div>{{ $logs->links() }}</div>
    </div>
</x-app-layout>
