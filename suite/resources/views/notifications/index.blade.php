<x-app-layout>
    <x-slot name="header">Notifications</x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-[#D4AF37]">All Notifications</h2>
                <p class="text-sm text-slate-400 mt-1">Recent system and application alerts appear here.</p>
            </div>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="text-sm px-4 py-2 border border-slate-700 text-slate-300 hover:bg-slate-800 rounded-lg transition-colors">
                    Mark all read
                </button>
            </form>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden divide-y divide-slate-800">
            @php
                $iconMap = [
                    'quote'   => '📄',
                    'payment' => '💳',
                    'invoice' => '🧾',
                    'client'  => '👤',
                    'bell'    => '🔔',
                ];
            @endphp
            @forelse($notifications as $notification)
                @php
                    $data = $notification->data;
                    $icon = $iconMap[$data['icon'] ?? 'bell'] ?? '🔔';
                @endphp
                <div class="flex items-start gap-4 px-5 py-4 {{ $notification->read_at ? '' : 'bg-[#001A3A]/30' }} hover:bg-slate-800/30">
                    <div class="text-2xl leading-none mt-0.5">{{ $icon }}</div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <a href="{{ $data['url'] ?? '#' }}" class="font-medium text-white hover:text-[#0078D4]">
                                {{ $data['title'] ?? class_basename($notification->type) }}
                            </a>
                            @if(!$notification->read_at)
                                <span class="shrink-0 w-2 h-2 bg-[#0078D4] rounded-full mt-1.5"></span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-400 mt-0.5">{{ $data['message'] ?? '' }}</p>
                        <p class="text-xs text-slate-600 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                <button class="text-xs text-slate-400 hover:text-white">Mark read</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-400 hover:text-red-300">Dismiss</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center text-slate-400 text-sm">You have no notifications.</div>
            @endforelse
        </div>

        {{ $notifications->links() }}
    </div>
</x-app-layout>
