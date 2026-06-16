<x-app-layout>
    <x-slot name="header">My Portal</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        <div>
            <h2 class="text-xl font-bold text-white">Welcome, {{ auth()->user()->name }}</h2>
            <p class="text-sm text-slate-400 mt-1">{{ $tenant->name }} · Platform overview</p>
        </div>

        {{-- Stats row --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="{{ $platformUnread > 0 ? 'bg-indigo-900/30 border-indigo-700' : 'bg-slate-900 border-slate-800' }} border rounded-xl p-4">
                <p class="text-xs {{ $platformUnread > 0 ? 'text-indigo-400' : 'text-slate-400' }} uppercase tracking-wide">Platform Messages</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $platformUnread }}</p>
                <p class="text-xs text-slate-500 mt-1">unread</p>
            </div>
            @if($hasClientMessaging)
            <div class="{{ $clientUnread > 0 ? 'bg-amber-900/30 border-amber-700' : 'bg-slate-900 border-slate-800' }} border rounded-xl p-4">
                <p class="text-xs {{ $clientUnread > 0 ? 'text-amber-400' : 'text-slate-400' }} uppercase tracking-wide">Client Messages</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $clientUnread }}</p>
                <p class="text-xs text-slate-500 mt-1">unread</p>
            </div>
            @else
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 opacity-50">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Client Messages</p>
                <p class="text-2xl font-bold text-slate-600 mt-1">—</p>
                <p class="text-xs text-slate-600 mt-1">module not active</p>
            </div>
            @endif
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Notifications</p>
                <p class="text-2xl font-bold text-white mt-1">{{ $unreadNotifCount }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
                <p class="text-xs text-slate-400 uppercase tracking-wide">Plan</p>
                <p class="text-sm font-bold text-white mt-1">{{ ucfirst($tenant->plan ?? 'Standard') }}</p>
            </div>
        </div>

        {{-- Message CTAs --}}
        @if($platformUnread > 0)
            <a href="{{ route('portal.messages') }}#platform"
               class="flex items-center justify-between px-5 py-4 bg-indigo-900/30 border border-indigo-700 rounded-xl hover:bg-indigo-900/50 transition-colors">
                <div>
                    <p class="font-semibold text-indigo-300">{{ $platformUnread }} unread message{{ $platformUnread !== 1 ? 's' : '' }} from Xquisite</p>
                    <p class="text-sm text-indigo-400/80">Click to view your platform messages</p>
                </div>
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endif

        @if($hasClientMessaging && $clientUnread > 0)
            <a href="{{ route('portal.messages') }}#clients"
               class="flex items-center justify-between px-5 py-4 bg-amber-900/30 border border-amber-700 rounded-xl hover:bg-amber-900/50 transition-colors">
                <div>
                    <p class="font-semibold text-amber-300">{{ $clientUnread }} unread client message{{ $clientUnread !== 1 ? 's' : '' }}</p>
                    <p class="text-sm text-amber-400/80">Click to view your client messages</p>
                </div>
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endif

        {{-- Quick nav --}}
        <div class="flex gap-3">
            <a href="{{ route('portal.messages') }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-sm text-slate-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Messages
            </a>
            <a href="{{ route('billing.index') }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-sm text-slate-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Billing
            </a>
            <a href="{{ route('settings.modules.index') }}"
               class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-sm text-slate-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Modules
            </a>
        </div>

        {{-- Recent notifications --}}
        @if($recentNotifications->count())
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-800">
                    <p class="font-semibold text-white text-sm">Recent Notifications</p>
                </div>
                @foreach($recentNotifications as $notification)
                    @php $data = $notification->data; @endphp
                    <a href="{{ $data['url'] ?? '#' }}" class="flex items-center gap-3 px-5 py-3 border-b border-slate-800 last:border-0 hover:bg-slate-800/40 text-sm">
                        <div class="flex-1">
                            <p class="text-white font-medium">{{ $data['title'] ?? '' }}</p>
                            <p class="text-slate-400 text-xs mt-0.5">{{ $data['message'] ?? '' }}</p>
                        </div>
                        <span class="text-xs text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                    </a>
                @endforeach
                <div class="px-5 py-3">
                    <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-400 hover:underline">View all</a>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
