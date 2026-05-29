<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">Log #{{ $log->id }}</h2>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-green-900/30 border border-green-700 text-green-300 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Status update form --}}
        @if($log->status !== 'resolved')
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-4">Update Status</h3>
            <form method="POST" action="{{ route('admin.logs.status', $log) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="flex gap-3 flex-wrap">
                    @foreach(['acknowledged','in_progress','resolved'] as $s)
                        <button type="submit" name="status" value="{{ $s }}"
                                class="px-4 py-2 rounded-lg text-sm font-medium
                                    @if($s === 'acknowledged') bg-yellow-700 hover:bg-yellow-600 text-white
                                    @elseif($s === 'in_progress') bg-blue-700 hover:bg-blue-600 text-white
                                    @else bg-green-700 hover:bg-green-600 text-white @endif
                                    {{ $log->status === $s ? 'ring-2 ring-white/50' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </button>
                    @endforeach
                </div>
                <textarea name="resolution_note" rows="2"
                          placeholder="Optional note (required when resolving)…"
                          class="w-full bg-slate-700 border-slate-600 text-slate-200 rounded-lg text-sm">{{ old('resolution_note') }}</textarea>
            </form>
        </div>
        @else
        <div class="bg-green-900/20 border border-green-700 rounded-xl p-4 text-sm text-green-300">
            Resolved {{ $log->resolved_at?->format('d M Y H:i') }}
            @if($log->resolvedBy) by {{ $log->resolvedBy->name }} @endif
            @if($log->resolution_note) — "{{ $log->resolution_note }}" @endif
        </div>
        @endif

        {{-- Log details --}}
        <div class="bg-slate-800 rounded-xl divide-y divide-slate-700">
            @foreach([
                'Level'      => $log->level,
                'Status'     => ucfirst(str_replace('_', ' ', $log->status)),
                'Source'     => $log->source,
                'Message'    => $log->message,
                'URL'        => $log->url ?? '—',
                'IP Address' => $log->ip_address ?? '—',
                'File'       => $log->file ? $log->file . ':' . $log->line : '—',
                'User ID'    => $log->user_id ?? 'guest',
                'Logged at'  => $log->created_at->format('d M Y H:i:s'),
            ] as $label => $value)
            <div class="px-6 py-3 flex gap-4">
                <dt class="w-32 text-xs text-slate-400 font-medium uppercase shrink-0">{{ $label }}</dt>
                <dd class="text-sm text-slate-200 break-all">{{ $value }}</dd>
            </div>
            @endforeach
        </div>

        {{-- Context --}}
        @if($log->context)
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-sm font-semibold text-slate-300 mb-3">Context</h3>
            <pre class="text-xs text-slate-300 overflow-auto bg-slate-900 p-4 rounded-lg">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif

    </div>
</x-app-layout>
