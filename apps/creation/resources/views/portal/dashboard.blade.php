<x-app-layout>
<div class="min-h-screen bg-slate-50">

    {{-- HERO --}}
    <div class="bg-slate-900 text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-1">Client Portal</p>
            <h1 class="text-3xl font-black tracking-tighter">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="text-slate-400 text-sm mt-1">{{ $client->company->name ?? 'Your service provider' }}</p>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        {{-- STATS ROW --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach([
                ['label' => 'Quotes',       'value' => $client->quotes->count(),            'color' => 'indigo'],
                ['label' => 'Invoices',      'value' => $client->invoices->count(),           'color' => 'sky'],
                ['label' => 'Unread Msgs',   'value' => $unreadMessages,                     'color' => 'amber'],
                ['label' => 'Notifications', 'value' => $unreadNotifCount,                   'color' => 'rose'],
            ] as $s)
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ $s['label'] }}</p>
                <p class="text-2xl font-black text-slate-900 tabular-nums">{{ $s['value'] }}</p>
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            {{-- NOTIFICATIONS FEED --}}
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-black text-slate-900 text-sm uppercase tracking-wider">Recent Notifications</h2>
                    <a href="{{ route('notifications.index') }}" class="text-[11px] font-bold text-slate-400 hover:text-slate-700 uppercase tracking-widest">See all</a>
                </div>

                @forelse($recentNotifications as $notification)
                @php
                    $data = $notification->data;
                    $iconMap = [
                        'quote'   => ['path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'text-indigo-500 bg-indigo-50'],
                        'payment' => ['path' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'text-emerald-500 bg-emerald-50'],
                        'invoice' => ['path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'text-sky-500 bg-sky-50'],
                        'message' => ['path' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'color' => 'text-amber-500 bg-amber-50'],
                        'bell'    => ['path' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color' => 'text-slate-500 bg-slate-100'],
                    ];
                    $ic = $iconMap[$data['icon'] ?? 'bell'] ?? $iconMap['bell'];
                @endphp
                <a href="{{ $data['url'] ?? '#' }}"
                   class="flex items-start gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 {{ $notification->read_at ? 'opacity-60' : '' }}">
                    <div class="flex-shrink-0 w-8 h-8 rounded-lg {{ $ic['color'] }} flex items-center justify-center mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ic['path'] }}"></path></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-slate-900">{{ $data['title'] ?? '' }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">{{ $data['body'] ?? '' }}</p>
                        <p class="text-[10px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if(!$notification->read_at)
                        <span class="w-2 h-2 rounded-full bg-rose-500 mt-1.5 flex-shrink-0"></span>
                    @endif
                </a>
                @empty
                <div class="px-5 py-10 text-center text-xs text-slate-400">No notifications yet.</div>
                @endforelse
            </div>

            {{-- RECENT QUOTES & INVOICES --}}
            <div class="space-y-6">

                {{-- QUOTES --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h2 class="font-black text-slate-900 text-sm uppercase tracking-wider">My Quotes</h2>
                    </div>
                    @forelse($client->quotes->take(4) as $quote)
                    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-50 last:border-0">
                        <div>
                            <p class="text-xs font-bold text-slate-900">{{ $quote->quote_number }}</p>
                            <p class="text-[10px] text-slate-400 capitalize">{{ $quote->status }}</p>
                        </div>
                        <span class="text-sm font-black text-slate-700 tabular-nums">R{{ number_format($quote->total, 2) }}</span>
                    </div>
                    @empty
                    <p class="px-5 py-6 text-xs text-slate-400 text-center">No quotes yet.</p>
                    @endforelse
                </div>

                {{-- INVOICES --}}
                <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h2 class="font-black text-slate-900 text-sm uppercase tracking-wider">My Invoices</h2>
                    </div>
                    @forelse($client->invoices->take(4) as $invoice)
                    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-50 last:border-0">
                        <div>
                            <p class="text-xs font-bold text-slate-900">{{ $invoice->invoice_number }}</p>
                            <p class="text-[10px] text-slate-400 capitalize">{{ $invoice->status }}</p>
                        </div>
                        <span class="text-sm font-black tabular-nums {{ $invoice->status === 'paid' ? 'text-emerald-600' : 'text-slate-700' }}">
                            R{{ number_format($invoice->total, 2) }}
                        </span>
                    </div>
                    @empty
                    <p class="px-5 py-6 text-xs text-slate-400 text-center">No invoices yet.</p>
                    @endforelse
                </div>

                {{-- MESSAGES CTA --}}
                @if($unreadMessages > 0)
                <a href="{{ route('portal.messages') }}"
                   class="flex items-center gap-4 bg-amber-50 border border-amber-200 rounded-2xl p-4 hover:bg-amber-100 transition-colors">
                    <div class="w-10 h-10 bg-amber-400 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    <div>
                        <p class="font-black text-amber-900 text-sm">{{ $unreadMessages }} unread {{ Str::plural('message', $unreadMessages) }}</p>
                        <p class="text-xs text-amber-700">Tap to view and reply →</p>
                    </div>
                </a>
                @else
                <a href="{{ route('portal.messages') }}"
                   class="flex items-center gap-3 border border-slate-200 bg-white rounded-2xl p-4 hover:border-slate-400 transition-colors shadow-sm">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    <span class="text-sm font-bold text-slate-600">Messages →</span>
                </a>
                @endif

            </div>
        </div>
    </div>
</div>
</x-app-layout>
