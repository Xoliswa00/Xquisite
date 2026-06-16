<x-app-layout>
    <x-slot name="header">Clients</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-white">Clients</h2>
            <a href="{{ route('clients.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition-colors">
                + Add Client
            </a>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            @forelse($clients as $client)
                <div class="flex items-center gap-4 px-5 py-4 border-b border-slate-800 last:border-0 hover:bg-slate-800/30">
                    <div class="w-9 h-9 rounded-full bg-indigo-800 flex items-center justify-center text-sm font-bold text-white shrink-0">
                        {{ strtoupper(substr($client->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-white">{{ $client->name }}</p>
                        <p class="text-sm text-slate-400">{{ $client->email }} {{ $client->phone ? '· ' . $client->phone : '' }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('clients.show', $client) }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-700">View</a>
                        <a href="{{ route('clients.messages', $client) }}" class="text-xs px-3 py-1.5 rounded-lg border border-indigo-700 text-indigo-400 hover:bg-indigo-900/30">Messages</a>
                    </div>
                </div>
            @empty
                <div class="px-5 py-10 text-center text-slate-400 text-sm">No clients yet. <a href="{{ route('clients.create') }}" class="text-indigo-400 hover:underline">Add one.</a></div>
            @endforelse
        </div>

        {{ $clients->links() }}
    </div>
</x-app-layout>
