<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">System Logs</h2>
                @if($unresolved > 0)
                    <p class="text-sm text-red-600 mt-0.5">{{ $unresolved }} unresolved critical error{{ $unresolved > 1 ? 's' : '' }}</p>
                @endif
            </div>
            <a href="{{ route('logs.audit') }}"
               class="px-3 py-2 bg-slate-800 text-white text-sm rounded-lg hover:bg-slate-700">
                Audit Trail
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-300 text-green-700 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="level" class="border-gray-300 rounded-lg text-sm">
                <option value="">All Levels</option>
                @foreach(['DEBUG','INFO','WARNING','ERROR','CRITICAL','ALERT','EMERGENCY'] as $lvl)
                    <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                @endforeach
            </select>

            <select name="status" class="border-gray-300 rounded-lg text-sm">
                <option value="">All Statuses</option>
                @foreach(['new','acknowledged','in_progress','resolved'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search message…"
                   class="border-gray-300 rounded-lg text-sm flex-1 min-w-48">

            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm hover:bg-slate-700">Filter</button>
            <a href="{{ route('logs.index') }}" class="px-4 py-2 text-gray-500 rounded-lg text-sm hover:text-gray-700">Clear</a>
        </form>

        <div class="bg-white shadow-sm rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Level</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Message</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Status</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Time</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-bold
                                    @if(in_array($log->level, ['ERROR','CRITICAL','ALERT','EMERGENCY'])) bg-red-100 text-red-700
                                    @elseif($log->level === 'WARNING') bg-yellow-100 text-yellow-700
                                    @elseif($log->level === 'INFO') bg-blue-100 text-blue-700
                                    @else bg-gray-100 text-gray-600 @endif">
                                    {{ $log->level }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-900 max-w-xs truncate">{{ $log->message }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($log->status === 'new') bg-red-100 text-red-700
                                    @elseif($log->status === 'acknowledged') bg-yellow-100 text-yellow-700
                                    @elseif($log->status === 'in_progress') bg-blue-100 text-blue-700
                                    @else bg-green-100 text-green-700 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $log->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('logs.show', $log) }}"
                                   class="text-blue-600 hover:text-blue-800 text-xs font-medium">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400">No logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $logs->links() }}</div>
    </div>
</x-app-layout>
