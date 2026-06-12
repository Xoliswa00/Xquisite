@extends('layouts.booking')

@section('content')
<div class="space-y-8">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Notifications</h1>
            <p class="text-sm text-slate-500 mt-0.5">All activity related to your appointments and booking updates.</p>
        </div>
        <a href="{{ route('book.my-bookings', $slug) }}"
           class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            My Bookings
        </a>
    </div>

    <div class="flex items-center justify-between gap-4">
        <p class="text-sm text-slate-500">Showing your latest notifications and unread history.</p>
        <form method="POST" action="{{ route('book.notifications.read-all', $slug) }}">
            @csrf
            <button type="submit" class="text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 px-4 py-2 rounded-lg">
                Mark all read
            </button>
        </form>
    </div>

    @if($notifications->isEmpty())
        <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center text-slate-500">
            <p class="font-semibold text-slate-900">No notifications yet.</p>
            <p class="mt-2">Booking updates and status changes will appear here.</p>
        </div>
    @else
        <div class="rounded-3xl border border-slate-200 bg-white overflow-hidden">
            <div class="divide-y divide-slate-200">
                @foreach($notifications as $notification)
                    @php $data = $notification->data; @endphp
                    <div class="px-5 py-4 {{ $notification->read_at ? 'bg-slate-50' : 'bg-slate-100' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $data['title'] ?? class_basename($notification->type) }}</p>
                                <p class="text-sm text-slate-500 mt-1 truncate">{{ $data['message'] ?? 'No details available.' }}</p>
                            </div>
                            <span class="text-xs uppercase tracking-wide text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>
                        @if(! empty($data['url']))
                            <div class="mt-3">
                                
                                <a href="{{ $data['url'] }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">View details</a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
