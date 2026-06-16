<x-app-layout>
    <x-slot name="header">{{ $client->name }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-5">
        <div class="bg-slate-800 rounded-xl p-5 space-y-4">
            <div class="flex items-start gap-3">
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-white truncate">{{ $client->name }}</h2>
                    <p class="text-sm text-slate-400">{{ $client->email }}{{ $client->phone ? ' · ' . $client->phone : '' }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('clients.messages', $client) }}" class="flex-1 sm:flex-none text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg">Messages</a>
                <a href="{{ route('clients.edit', $client) }}" class="flex-1 sm:flex-none text-center px-4 py-2 border border-slate-700 text-slate-300 hover:bg-slate-800 text-sm rounded-lg">Edit</a>
            </div>
        </div>

        @if($client->notes)
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
                <p class="text-sm text-slate-300">{{ $client->notes }}</p>
            </div>
        @endif

        <div class="text-sm text-slate-500">
            Added {{ $client->created_at->diffForHumans() }}
        </div>
    </div>
</x-app-layout>
