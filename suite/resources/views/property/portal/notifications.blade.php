@extends('property.portal.layout')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                Notifications
                @if($unreadCount > 0)
                    <span class="inline-flex items-center justify-center text-[11px] font-bold px-2 py-0.5 rounded-full bg-[#0078D4] text-white leading-none">
                        {{ $unreadCount }}
                    </span>
                @endif
            </h1>
            <p class="text-sm text-slate-500 mt-0.5">Rent reminders, payments, and lease updates</p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('rent.notifications.read-all', $slug) }}">
                @csrf
                <button type="submit" class="text-xs px-4 py-2 border border-slate-300 text-slate-600 hover:bg-slate-50 rounded-lg transition-colors">
                    Mark all read
                </button>
            </form>
        @endif
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        @forelse($notifications as $notification)
            @php
                $data = $notification->data;
                $isRead = (bool) $notification->read_at;
                $url = $data['url'] ?? null;
                $title = $data['title'] ?? 'Notification';
                $message = $data['message'] ?? '';
            @endphp
            <div class="flex items-start gap-4 px-5 py-4 border-b border-slate-100 last:border-b-0 {{ $isRead ? '' : 'bg-[#0078D4]/5' }}">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        @if($url)
                            <a href="{{ $url }}" class="font-semibold text-sm {{ $isRead ? 'text-slate-700 hover:text-slate-900' : 'text-slate-900 hover:text-[#0078D4]' }}">{{ $title }}</a>
                        @else
                            <span class="font-semibold text-sm {{ $isRead ? 'text-slate-700' : 'text-slate-900' }}">{{ $title }}</span>
                        @endif
                        @if(!$isRead)
                            <span class="w-2 h-2 rounded-full bg-[#0078D4]"></span>
                        @endif
                    </div>
                    @if($message)
                        <p class="text-sm text-slate-500 mt-0.5">{{ $message }}</p>
                    @endif
                    <p class="text-xs text-slate-400 mt-1.5">{{ $notification->created_at->diffForHumans() }}</p>
                </div>
                @if(!$isRead)
                    <form method="POST" action="{{ route('rent.notifications.read', [$slug, $notification->id]) }}">
                        @csrf
                        <button type="submit" class="text-xs text-slate-400 hover:text-slate-700 whitespace-nowrap">Mark read</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <p class="text-slate-500 font-medium">No notifications yet</p>
                <p class="text-slate-400 text-sm mt-1">Rent reminders and payment confirmations will appear here.</p>
            </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif

</div>
@endsection
