<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Log #{{ $log->id }}</h2>
            <a href="{{ route('logs.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-300 text-green-700 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($log->status !== 'resolved')
        <div class="bg-white shadow-sm rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Update Status</h3>
            <form method="POST" action="{{ route('logs.status', $log) }}" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="flex gap-3 flex-wrap">
                    @foreach(['acknowledged' => 'bg-yellow-600', 'in_progress' => 'bg-blue-600', 'resolved' => 'bg-green-600'] as $s => $color)
                        <button type="submit" name="status" value="{{ $s }}"
                                class="px-4 py-2 rounded-lg text-sm font-medium text-white {{ $color }} hover:opacity-90
                                       {{ $log->status === $s ? 'ring-2 ring-offset-1 ring-current' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </button>
                    @endforeach
                </div>
                <textarea name="resolution_note" rows="2"
                          placeholder="Optional note…"
                          class="w-full border-gray-300 rounded-lg text-sm">{{ old('resolution_note') }}</textarea>
            </form>
        </div>
        @else
        <div class="bg-green-50 border border-green-300 rounded-xl p-4 text-sm text-green-700">
            Resolved {{ $log->resolved_at?->format('d M Y H:i') }}
            @if($log->resolvedBy) by {{ $log->resolvedBy->name }} @endif
            @if($log->resolution_note) — "{{ $log->resolution_note }}" @endif
        </div>
        @endif

        <div class="bg-white shadow-sm rounded-xl divide-y">
            @foreach([
                'Level'      => $log->level,
                'Status'     => ucfirst(str_replace('_', ' ', $log->status)),
                'Message'    => $log->message,
                'URL'        => $log->url ?? '—',
                'IP Address' => $log->ip_address ?? '—',
                'File'       => $log->file ? $log->file . ':' . $log->line : '—',
                'User ID'    => $log->user_id ?? 'guest',
                'Logged at'  => $log->created_at->format('d M Y H:i:s'),
            ] as $label => $value)
            <div class="px-6 py-3 flex gap-4">
                <dt class="w-32 text-xs text-gray-400 font-medium uppercase shrink-0">{{ $label }}</dt>
                <dd class="text-sm text-gray-900 break-all">{{ $value }}</dd>
            </div>
            @endforeach
        </div>

        @if($log->context)
        <div class="bg-white shadow-sm rounded-xl p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Context</h3>
            <pre class="text-xs text-gray-700 overflow-auto bg-gray-50 p-4 rounded-lg">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
        </div>
        @endif

    </div>
</x-app-layout>
