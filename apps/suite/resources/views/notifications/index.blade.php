<x-app-layout>
    <x-slot name="header">Notifications</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-white">Your notifications</h2>
                <p class="text-sm text-slate-400">Recent system and application alerts appear here. Mark them read when you have reviewed them.</p>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 hover:bg-emerald-400 transition-colors">
                    Mark all read
                </button>
            </form>
        </div>

        @if($notifications->isEmpty())
            <div class="rounded-3xl border border-slate-800 bg-slate-900 p-6 text-slate-400">
                <p class="font-medium text-slate-200">You have no notifications yet.</p>
                <p class="mt-2 text-sm">Notifications will appear here when there are important updates, stock alerts, or booking events.</p>
            </div>
        @else
            <div class="rounded-3xl border border-slate-800 bg-slate-900 overflow-hidden">
                <div class="divide-y divide-slate-800">
                    @foreach($notifications as $notification)
                        @php $data = $notification->data; @endphp
                        <a href="{{ $data['url'] ?? '#' }}" class="block px-5 py-4 hover:bg-slate-800 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="font-semibold text-white truncate">{{ $data['title'] ?? class_basename($notification->type) }}</p>
                                    <p class="mt-1 text-sm text-slate-400 truncate">{{ $data['message'] ?? 'No details available.' }}</p>
                                </div>
                                <span class="text-xs uppercase tracking-widest text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
