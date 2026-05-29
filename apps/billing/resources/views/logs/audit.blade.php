<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Audit Trail</h2>
            <a href="{{ route('logs.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; System Logs</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="action" value="{{ request('action') }}"
                   placeholder="Filter by action…"
                   class="border-gray-300 rounded-lg text-sm flex-1 min-w-48">
            <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm">Filter</button>
            <a href="{{ route('logs.audit') }}" class="px-4 py-2 text-gray-500 rounded-lg text-sm hover:text-gray-700">Clear</a>
        </form>

        <div class="bg-white shadow-sm rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Action</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Entity</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">User</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">IP</th>
                        <th class="px-4 py-3 text-left text-gray-600 font-medium">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-gray-900">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $log->entity_type }}
                                @if($log->entity_id) <span class="text-gray-400">#{{ $log->entity_id }}</span> @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700 text-xs">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400">No audit records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $logs->links() }}</div>
    </div>
</x-app-layout>
