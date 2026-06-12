<x-app-layout>
<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

    <div class="flex items-end justify-between border-b border-slate-200 pb-8">
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-2">Owner Dashboard</p>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Notifications</h2>
        </div>
        @if(auth()->user()->unreadNotifications->count() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button class="text-xs font-bold text-slate-500 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-4 py-2 rounded-xl transition-all">
                Mark all as read
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    <div class="space-y-2">
        @forelse($notifications as $notification)
        @php
            $data = $notification->data;
            $icon = $data['icon'] ?? 'bell';
            $iconMap = [
                'quote'   => ['path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'text-indigo-500 bg-indigo-50 border-indigo-100'],
                'payment' => ['path' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'text-emerald-500 bg-emerald-50 border-emerald-100'],
                'client'  => ['path' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color' => 'text-amber-500 bg-amber-50 border-amber-100'],
                'invoice' => ['path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'text-sky-500 bg-sky-50 border-sky-100'],
                'bell'    => ['path' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color' => 'text-slate-500 bg-slate-100 border-slate-200'],
            ];
            $ic = $iconMap[$icon] ?? $iconMap['bell'];
            $isUnread = is_null($notification->read_at);
        @endphp

        <div class="bg-white border rounded-2xl shadow-sm transition-all {{ $isUnread ? 'border-slate-300 shadow-slate-100' : 'border-slate-100' }}">
            <div class="flex items-start gap-4 p-5">

                <div class="flex-shrink-0 w-10 h-10 rounded-xl border {{ $ic['color'] }} flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ic['path'] }}"></path>
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-sm font-black text-slate-900">{{ $data['title'] ?? 'Notification' }}</p>
                        @if($isUnread)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-rose-50 text-rose-500 border border-rose-100">
                            New
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-500">{{ $data['body'] ?? '' }}</p>
                    <p class="text-[11px] text-slate-400 mt-1.5 font-medium">{{ $notification->created_at->diffForHumans() }} · {{ $notification->created_at->format('d M Y, H:i') }}</p>
                </div>

                <div class="flex-shrink-0 flex items-center gap-2">
                    @if(!empty($data['url']))
                    <a href="{{ $data['url'] }}"
                       class="text-xs font-bold text-slate-600 hover:text-slate-900 border border-slate-200 hover:border-slate-400 px-3 py-1.5 rounded-lg transition-all">
                        View →
                    </a>
                    @endif
                    <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-slate-300 hover:text-red-500 transition-colors p-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-20">
            <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            <p class="font-bold text-slate-400 text-sm uppercase tracking-widest">All caught up</p>
            <p class="text-slate-400 text-xs mt-1">New notifications will appear here.</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="border-t border-slate-100 pt-6">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
</x-app-layout>
