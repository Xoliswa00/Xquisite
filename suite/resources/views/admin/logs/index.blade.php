<x-app-layout>
    <x-slot name="header">System Logs</x-slot>

    <div class="max-w-7xl mx-auto space-y-6"
         x-data="{
             selected: [],
             allIds: {{ $logs->pluck('id')->toJson() }},
             get allChecked() { return this.allIds.length > 0 && this.allIds.every(id => this.selected.includes(id)); },
             toggleAll() { this.selected = this.allChecked ? [] : [...this.allIds]; },
             bulkAction: 'resolved',
         }">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-xl font-bold text-[#D4AF37]">System Logs</h1>
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
                   class="px-3 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm rounded-lg transition-colors">
                    Combined View
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-900/30 border border-green-700 text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Auto-filters (no submit button) --}}
        <form method="GET" id="filter-form" class="flex flex-wrap gap-3 items-center">
            <select name="level"
                    class="bg-slate-800 border border-slate-600 text-slate-300 rounded-lg text-sm px-3 py-2"
                    onchange="this.form.submit()">
                <option value="">All Levels</option>
                @foreach($levels as $lvl)
                    <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                @endforeach
            </select>

            <select name="status"
                    class="bg-slate-800 border border-slate-600 text-slate-300 rounded-lg text-sm px-3 py-2"
                    onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                @endforeach
            </select>

            <select name="source"
                    class="bg-slate-800 border border-slate-600 text-slate-300 rounded-lg text-sm px-3 py-2"
                    onchange="this.form.submit()">
                <option value="">All Sources</option>
                @foreach($sources as $src)
                    <option value="{{ $src }}" {{ request('source') === $src ? 'selected' : '' }}>{{ ucfirst(str_replace('-', ' ', $src)) }}</option>
                @endforeach
            </select>

            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search message…"
                   class="bg-slate-800 border border-slate-600 text-slate-300 rounded-lg text-sm px-3 py-2 flex-1 min-w-48"
                   oninput="clearTimeout(this._t); this._t = setTimeout(() => this.form.submit(), 400)">

            @if(request()->hasAny(['level','status','source','search']))
                <a href="{{ route('admin.logs.index') }}"
                   class="px-3 py-2 text-slate-400 hover:text-white text-sm rounded-lg transition-colors">
                    Clear
                </a>
            @endif

            {{-- Resolve all matching current filters --}}
            <form method="POST" action="{{ route('admin.logs.resolve-all') }}" class="inline"
                  onsubmit="return confirm('Resolve all logs matching current filters?')">
                @csrf
                @foreach(request()->only(['level','status','source','search']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <button type="submit"
                        class="px-3 py-2 bg-slate-700 hover:bg-green-800 text-slate-300 hover:text-green-200 text-sm rounded-lg transition-colors whitespace-nowrap">
                    Resolve all
                </button>
            </form>
        </form>

        {{-- Bulk action bar (shown when rows are selected) --}}
        <div x-show="selected.length > 0" x-cloak
             class="flex items-center gap-3 p-3 bg-slate-700 rounded-lg border border-slate-600">
            <span class="text-sm text-slate-300" x-text="selected.length + ' selected'"></span>
            <select x-model="bulkAction"
                    class="bg-slate-800 border border-slate-600 text-slate-300 rounded-lg text-sm px-3 py-1.5">
                <option value="resolved">Resolve</option>
                <option value="acknowledged">Acknowledge</option>
                <option value="in_progress">Mark In Progress</option>
            </select>
            <form method="POST" action="{{ route('admin.logs.bulk') }}"
                  @submit.prevent="
                      const form = $el;
                      selected.forEach(id => {
                          const input = document.createElement('input');
                          input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
                          form.appendChild(input);
                      });
                      form.submit();
                  ">
                @csrf
                <input type="hidden" name="action" :value="bulkAction">
                <button type="submit"
                        class="px-4 py-1.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm rounded-lg transition-colors">
                    Apply
                </button>
            </form>
            <button @click="selected = []" class="text-slate-400 hover:text-white text-sm ml-auto">Deselect all</button>
        </div>

        {{-- Table --}}
        <div class="bg-slate-800 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-slate-900/50 border-b border-slate-700">
                    <tr>
                        <th class="px-4 py-3 w-8">
                            <input type="checkbox" class="rounded bg-slate-700 border-slate-600 text-[#0078D4]"
                                   :checked="allChecked" @change="toggleAll()">
                        </th>
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
                        <tr class="hover:bg-slate-700/30" :class="selected.includes({{ $log->id }}) ? 'bg-slate-700/50' : ''">
                            <td class="px-4 py-3">
                                <input type="checkbox" class="rounded bg-slate-700 border-slate-600 text-[#0078D4]"
                                       :value="{{ $log->id }}" x-model="selected">
                            </td>
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
                                   class="text-[#0078D4] hover:text-[#B8D4F0] text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">No logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $logs->links() }}</div>
    </div>
</x-app-layout>
