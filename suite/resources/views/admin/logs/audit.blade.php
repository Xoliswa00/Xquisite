<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center gap-4 flex-wrap">
            <h2 class="text-xl font-bold text-[#D4AF37]">Audit Trail</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; System Logs</a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-5">

        {{-- Filters --}}
        <form method="GET" class="grid grid-cols-2 sm:flex flex-wrap gap-2 items-center">
            <input type="text" name="action" value="{{ request('action') }}"
                   placeholder="Action (e.g. User.created)…"
                   class="col-span-2 sm:col-span-1 bg-slate-800 border border-slate-600 text-slate-300 rounded-lg text-sm px-3 py-2 sm:flex-1 min-w-0">
            <input type="text" name="entity_type" value="{{ request('entity_type') }}"
                   placeholder="Entity type…"
                   class="bg-slate-800 border border-slate-600 text-slate-300 rounded-lg text-sm px-3 py-2">
            <button type="submit" class="px-4 py-2 bg-slate-700 text-white rounded-lg text-sm">Filter</button>
            <a href="{{ route('admin.logs.audit') }}" class="px-4 py-2 text-slate-400 rounded-lg text-sm hover:text-white">Clear</a>
        </form>

        {{-- ── DESKTOP TABLE ───────────────────────────────────────────── --}}
        <div class="hidden sm:block bg-slate-800 rounded-xl overflow-hidden shadow-sm">
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
                                @if($log->entity_id)<span class="text-slate-500"> #{{ $log->entity_id }}</span>@endif
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

        {{-- ── MOBILE CARDS ────────────────────────────────────────────── --}}
        <div class="block sm:hidden space-y-2">
            @forelse($logs as $log)
                <div class="bg-slate-800 rounded-xl px-4 py-3 space-y-1.5">
                    <div class="flex items-start justify-between gap-2">
                        <span class="font-mono text-xs text-slate-100 leading-snug break-all">{{ $log->action }}</span>
                        <span class="text-slate-500 text-xs whitespace-nowrap shrink-0 ml-2">
                            {{ $log->created_at->format('d M H:i') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">
                        {{ $log->entity_type }}@if($log->entity_id) <span class="text-slate-500">#{{ $log->entity_id }}</span>@endif
                    </p>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span>{{ $log->user?->name ?? 'System' }}</span>
                        @if($log->ip_address)
                            <span>&middot;</span>
                            <span>{{ $log->ip_address }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-slate-800 rounded-xl px-4 py-10 text-center text-slate-500 text-sm">
                    No audit records found.
                </div>
            @endforelse
        </div>

        <div>{{ $logs->links() }}</div>
    </div>
</x-app-layout>
