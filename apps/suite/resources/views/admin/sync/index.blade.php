<x-app-layout>
    <x-slot name="header">Billing Sync Queue</x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-xl font-bold text-white">Billing Sync Queue</h1>
                @if(($counts['pending'] + $counts['retrying']) > 0)
                    <p class="text-sm text-amber-400 mt-0.5">{{ $counts['pending'] + $counts['retrying'] }} item(s) waiting to sync</p>
                @else
                    <p class="text-sm text-emerald-400 mt-0.5">All synced with billing</p>
                @endif
            </div>
            @if(($counts['pending'] + $counts['abandoned']) > 0)
                <form method="POST" action="{{ route('admin.sync.retry-all') }}">
                    @csrf
                    <button class="shrink-0 px-4 py-2 bg-amber-600 hover:bg-amber-500 text-white text-sm font-semibold rounded-lg transition-colors">
                        Retry All Pending / Abandoned
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-900/30 border border-green-700 text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Status counters --}}
        <div class="grid grid-cols-4 gap-4">
            @foreach([
                'pending'   => ['label' => 'Pending',   'color' => 'yellow'],
                'retrying'  => ['label' => 'Retrying',  'color' => 'blue'],
                'completed' => ['label' => 'Completed', 'color' => 'green'],
                'abandoned' => ['label' => 'Abandoned', 'color' => 'red'],
            ] as $status => $meta)
            <a href="{{ route('admin.sync.index', ['status' => $status]) }}"
               class="bg-slate-800 rounded-xl p-4 hover:bg-slate-700 transition
                      {{ request('status') === $status ? 'ring-2 ring-white/20' : '' }}">
                <p class="text-xs text-slate-400 uppercase font-semibold">{{ $meta['label'] }}</p>
                <p class="text-3xl font-bold mt-1
                    @if($meta['color'] === 'yellow') text-yellow-400
                    @elseif($meta['color'] === 'blue') text-blue-400
                    @elseif($meta['color'] === 'green') text-green-400
                    @else text-red-400 @endif">
                    {{ $counts[$status] }}
                </p>
            </a>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <select name="status" class="bg-slate-800 border-slate-600 text-slate-300 rounded-lg text-sm">
                <option value="">All Statuses</option>
                @foreach(['pending','retrying','completed','abandoned'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <select name="type" class="bg-slate-800 border-slate-600 text-slate-300 rounded-lg text-sm">
                <option value="">All Types</option>
                <option value="create_subscription" {{ request('type') === 'create_subscription' ? 'selected' : '' }}>Create</option>
                <option value="cancel_subscription" {{ request('type') === 'cancel_subscription' ? 'selected' : '' }}>Cancel</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-slate-700 text-white rounded-lg text-sm hover:bg-slate-600">Filter</button>
            <a href="{{ route('admin.sync.index') }}" class="px-4 py-2 text-slate-400 rounded-lg text-sm hover:text-white">Clear</a>
        </form>

        {{-- Table --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Type</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Tenant</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Module</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Status</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Attempts</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Next Retry</th>
                        <th class="px-4 py-3 text-left text-slate-400 font-medium">Last Error</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($items as $item)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs font-mono
                                    {{ $item->type === 'create_subscription' ? 'bg-indigo-900/60 text-indigo-300' : 'bg-red-900/60 text-red-300' }}">
                                    {{ $item->type === 'create_subscription' ? 'create' : 'cancel' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-200">{{ $item->tenant?->name ?? "Tenant #{$item->tenant_id}" }}</td>
                            <td class="px-4 py-3 text-slate-300 font-mono text-xs">{{ $item->module_key }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded text-xs
                                    @if($item->status === 'pending') bg-yellow-900/40 text-yellow-300
                                    @elseif($item->status === 'retrying') bg-blue-900/40 text-blue-300
                                    @elseif($item->status === 'completed') bg-green-900/40 text-green-300
                                    @else bg-red-900/40 text-red-300 @endif">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">
                                {{ $item->attempts }} / {{ $item->max_attempts }}
                            </td>
                            <td class="px-4 py-3 text-slate-400 text-xs">
                                @if($item->status === 'completed')
                                    <span class="text-green-400">Done {{ $item->completed_at?->diffForHumans() }}</span>
                                @elseif($item->next_retry_at)
                                    {{ $item->next_retry_at->diffForHumans() }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-red-400 text-xs max-w-xs truncate" title="{{ $item->last_error }}">
                                {{ $item->last_error ? str($item->last_error)->limit(60) : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if(in_array($item->status, ['pending', 'abandoned']))
                                    <form method="POST" action="{{ route('admin.sync.retry', $item) }}" class="inline">
                                        @csrf
                                        <button class="text-yellow-400 hover:text-yellow-300 text-xs font-medium mr-3">
                                            Retry Now
                                        </button>
                                    </form>
                                @endif
                                @if($item->status !== 'completed')
                                    <form method="POST" action="{{ route('admin.sync.dismiss', $item) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="text-slate-500 hover:text-red-400 text-xs">
                                            Dismiss
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                                No sync items found. Billing is fully in sync.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $items->links() }}</div>

        <p class="text-xs text-slate-500">
            The scheduler automatically retries pending items every 5 minutes with exponential backoff
            (5 min → 10 → 20 → 40 → 60 min max). Items are abandoned after {{ 5 }} failed attempts.
            Use "Retry Now" to force an immediate attempt without waiting for the scheduler.
        </p>

    </div>
</x-app-layout>
