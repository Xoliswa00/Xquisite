<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">Audit Trail</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; System Logs</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto p-6 space-y-6">

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="action" value="{{ request('action') }}"
                   placeholder="Action (e.g. User.created)…"
                   class="bg-slate-800 border-slate-600 text-slate-300 rounded-lg text-sm flex-1 min-w-48">
            <input type="text" name="entity_type" value="{{ request('entity_type') }}"
                   placeholder="Entity type…"
                   class="bg-slate-800 border-slate-600 text-slate-300 rounded-lg text-sm">
            <button type="submit" class="px-4 py-2 bg-slate-700 text-white rounded-lg text-sm">Filter</button>
            <a href="{{ route('admin.logs.audit') }}" class="px-4 py-2 text-slate-400 rounded-lg text-sm hover:text-white">Clear</a>
        </form>

        <div class="bg-slate-800 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Action</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Entity</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">User</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">IP</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3 text-slate-200 font-mono text-xs">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">
                                {{ $log->entity_type }}
                                @if($log->entity_id) <span class="text-slate-500">#{{ $log->entity_id }}</span> @endif
                            </td>
                            <td class="px-4 py-3 text-slate-300 text-xs">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs whitespace-nowrap">
                                {{ $log->created_at->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500">No audit records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $logs->links() }}</div>
    </div>
</x-app-layout>
