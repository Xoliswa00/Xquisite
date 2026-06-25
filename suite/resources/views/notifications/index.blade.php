<x-app-layout>
    <x-slot name="header">Notifications</x-slot>

    <div class="max-w-3xl mx-auto space-y-5">

        {{-- ── Page heading ─────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-[#D4AF37] flex items-center gap-2">
                    Notifications
                    @if($unreadCount > 0)
                        <span class="inline-flex items-center justify-center text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#0078D4] text-white leading-none">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </h1>
                <p class="text-sm text-slate-500 mt-0.5">Bookings, payments, clients, and system alerts</p>
            </div>

            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit"
                            class="text-xs px-4 py-2 border border-slate-700 text-slate-300 hover:bg-slate-800 hover:border-slate-600 rounded-lg transition-colors">
                        Mark all read
                    </button>
                </form>
            @endif
        </div>

        {{-- ── Filter tabs ───────────────────────────────────────────────────── --}}
        <div class="flex items-center gap-1 bg-slate-900 border border-slate-800 rounded-xl p-1">
            <a href="{{ route('notifications.index', ['filter' => 'all']) }}"
               class="flex-1 text-center text-xs font-semibold py-2 rounded-lg transition
                      {{ $filter === 'all' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white' }}">
                All
                <span class="ml-1 text-slate-500">{{ $notifications->total() }}</span>
            </a>
            <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
               class="flex-1 text-center text-xs font-semibold py-2 rounded-lg transition
                      {{ $filter === 'unread' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-white' }}">
                Unread
                @if($unreadCount > 0)
                    <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-[#0078D4] text-white">{{ $unreadCount }}</span>
                @endif
            </a>
        </div>

        {{-- ── Notification list ────────────────────────────────────────────── --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            @forelse($notifications as $notification)
                @php
                    $data    = $notification->data;
                    $isRead  = (bool) $notification->read_at;
                    $icon    = $data['icon'] ?? 'bell';
                    $url     = $data['url'] ?? '#';
                    $title   = $data['title'] ?? class_basename($notification->type);
                    $message = $data['message'] ?? '';

                    $iconConfig = match($icon) {
                        'payment'     => ['bg' => 'bg-emerald-500/10', 'ring' => 'ring-emerald-500/20', 'color' => 'text-emerald-400',
                                          'path' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z'],
                        'invoice'     => ['bg' => 'bg-amber-500/10', 'ring' => 'ring-amber-500/20', 'color' => 'text-amber-400',
                                          'path' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
                        'quote'       => ['bg' => 'bg-purple-500/10', 'ring' => 'ring-purple-500/20', 'color' => 'text-purple-400',
                                          'path' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
                        'client'      => ['bg' => 'bg-[#0078D4]/10', 'ring' => 'ring-[#0078D4]/20', 'color' => 'text-[#0078D4]',
                                          'path' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
                        'message'     => ['bg' => 'bg-cyan-500/10', 'ring' => 'ring-cyan-500/20', 'color' => 'text-cyan-400',
                                          'path' => 'M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z'],
                        default       => ['bg' => 'bg-slate-700/50', 'ring' => 'ring-slate-600/20', 'color' => 'text-slate-400',
                                          'path' => 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0'],
                    };
                @endphp

                <div class="flex items-start gap-4 px-4 sm:px-5 py-4 border-b border-slate-800 last:border-b-0 transition-colors
                            {{ $isRead ? 'hover:bg-slate-800/20' : 'bg-[#001A3A]/30 hover:bg-[#001A3A]/50' }}">

                    {{-- Icon --}}
                    <div class="shrink-0 w-9 h-9 rounded-xl {{ $iconConfig['bg'] }} ring-1 {{ $iconConfig['ring'] }} flex items-center justify-center mt-0.5">
                        <svg class="w-4.5 h-4.5 {{ $iconConfig['color'] }}" style="width:1.125rem;height:1.125rem"
                             fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconConfig['path'] }}"/>
                        </svg>
                    </div>

                    {{-- Body --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start gap-2">
                            <a href="{{ $url }}"
                               class="font-semibold text-sm leading-snug {{ $isRead ? 'text-slate-300 hover:text-white' : 'text-white hover:text-[#0078D4]' }} transition-colors">
                                {{ $title }}
                            </a>
                            @if(!$isRead)
                                <span class="shrink-0 w-2 h-2 rounded-full bg-[#0078D4] mt-1.5"></span>
                            @endif
                        </div>
                        @if($message)
                            <p class="text-sm text-slate-400 mt-0.5 leading-snug">{{ $message }}</p>
                        @endif
                        <p class="text-xs text-slate-600 mt-1.5">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="shrink-0 flex flex-col sm:flex-row items-end sm:items-center gap-2 pt-0.5">
                        @if(!$isRead)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                <button type="submit"
                                        class="text-[11px] text-slate-400 hover:text-white transition-colors whitespace-nowrap">
                                    Mark read
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="text-[11px] text-red-500/70 hover:text-red-400 transition-colors">
                                Dismiss
                            </button>
                        </form>
                    </div>
                </div>

            @empty
                <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                    <div class="w-14 h-14 rounded-2xl bg-slate-800 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/>
                        </svg>
                    </div>
                    <p class="text-slate-400 font-medium">
                        {{ $filter === 'unread' ? 'No unread notifications' : 'No notifications yet' }}
                    </p>
                    <p class="text-slate-600 text-sm mt-1">
                        {{ $filter === 'unread' ? "You're all caught up." : 'Booking, payment, and system alerts will appear here.' }}
                    </p>
                    @if($filter === 'unread')
                        <a href="{{ route('notifications.index') }}"
                           class="mt-4 text-xs text-[#0078D4] hover:underline">View all notifications</a>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- ── Pagination ───────────────────────────────────────────────────── --}}
        @if($notifications->hasPages())
            <div class="pb-2">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
