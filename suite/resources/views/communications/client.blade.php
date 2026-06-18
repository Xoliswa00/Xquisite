<x-app-layout>
    <x-slot name="header">Messages</x-slot>

    <div class="max-w-3xl mx-auto space-y-5" x-data="{ tab: window.location.hash === '#clients' ? 'clients' : 'platform' }">

        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-[#D4AF37]">Messages</h2>
            <a href="{{ route('portal.dashboard') }}" class="text-sm text-slate-400 hover:text-white">← Dashboard</a>
        </div>

        {{-- Tab bar --}}
        <div class="flex gap-1 bg-slate-900 border border-slate-800 rounded-xl p-1">
            <button @click="tab = 'platform'; window.location.hash = 'platform'"
                    :class="tab === 'platform' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-300'"
                    class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Platform Support
                @if($platformMessages->where('is_from_owner', true)->whereNull('read_at')->count() > 0)
                    <span class="bg-[#0078D4] text-white text-xs rounded-full px-1.5 py-0.5">{{ $platformMessages->where('is_from_owner', true)->whereNull('read_at')->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'clients'; window.location.hash = 'clients'"
                    :class="tab === 'clients' ? 'bg-slate-700 text-white' : 'text-slate-400 hover:text-slate-300'"
                    class="flex-1 px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Client Messaging
                @if($hasClientMessaging && $clientThreads->sum('unread_count') > 0)
                    <span class="bg-amber-600 text-white text-xs rounded-full px-1.5 py-0.5">{{ $clientThreads->sum('unread_count') }}</span>
                @endif
            </button>
        </div>

        {{-- ── Platform Support Channel ── --}}
        <div x-show="tab === 'platform'" id="platform">

            {{-- Chat thread --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 min-h-64 max-h-[55vh] overflow-y-auto">
                @forelse($platformMessages as $msg)
                    @if($msg->is_from_owner)
                        {{-- From Xquisite --}}
                        <div class="flex justify-start">
                            <div class="max-w-[75%]">
                                @if($msg->subject)
                                    <p class="text-xs text-slate-400 mb-1 font-medium">{{ $msg->subject }}</p>
                                @endif
                                <div class="bg-[#001A3A]/40 border border-[#002B5B]/50 text-slate-200 rounded-2xl rounded-tl-none px-4 py-3 text-sm">
                                    {!! nl2br(e($msg->body)) !!}
                                </div>
                                <p class="text-[10px] text-slate-500 mt-1">{{ $msg->created_at->diffForHumans() }} · Xquisite Support</p>
                            </div>
                        </div>
                    @else
                        {{-- From tenant --}}
                        <div class="flex justify-end">
                            <div class="max-w-[75%]">
                                <div class="bg-slate-700 text-white rounded-2xl rounded-tr-none px-4 py-3 text-sm">
                                    {!! nl2br(e($msg->body)) !!}
                                </div>
                                <p class="text-[10px] text-slate-500 text-right mt-1">{{ $msg->created_at->diffForHumans() }} · You</p>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="text-center text-sm text-slate-500 py-8">No messages yet. Send us a message below.</p>
                @endforelse
            </div>

            {{-- Reply form --}}
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 mt-4">
                <form method="POST" action="{{ route('portal.messages.reply') }}" class="space-y-3">
                    @csrf
                    <textarea name="body" rows="3" required
                              class="w-full rounded-lg bg-slate-800 border-slate-700 text-slate-200 text-sm focus:ring-[#0078D4] focus:border-[#0078D4]"
                              placeholder="Message Xquisite Support…"></textarea>
                    <x-input-error :messages="$errors->get('body')" />
                    <button type="submit" class="px-5 py-2.5 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm font-medium rounded-lg">Send</button>
                </form>
            </div>

        </div>

        {{-- ── Client Messaging Channel ── --}}
        <div x-show="tab === 'clients'" id="clients">

            @if(!$hasClientMessaging)
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">
                    <svg class="w-10 h-10 text-slate-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <p class="text-slate-400 font-medium">Client Messaging is not active</p>
                    <p class="text-sm text-slate-500 mt-1">Activate this module to message your business clients directly.</p>
                    <a href="{{ route('settings.modules.index') }}" class="mt-4 inline-block px-5 py-2.5 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm font-medium rounded-lg">
                        View Modules
                    </a>
                </div>
            @elseif($clientThreads->isEmpty())
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-8 text-center">
                    <p class="text-slate-400">No clients yet. <a href="{{ route('clients.create') }}" class="text-[#0078D4] hover:underline">Add your first client →</a></p>
                </div>
            @else
                {{-- Client list with last message preview --}}
                <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                    @foreach($clientThreads as $client)
                        @php $last = $client->communications->first(); @endphp
                        <a href="{{ route('clients.messages', $client) }}"
                           class="flex items-center gap-4 px-5 py-4 border-b border-slate-800 last:border-0 hover:bg-slate-800/50 transition-colors">
                            <div class="w-9 h-9 rounded-full bg-[#002B5B] flex items-center justify-center text-sm font-bold text-white shrink-0">
                                {{ strtoupper(substr($client->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-white">{{ $client->name }}</p>
                                    @if($last)
                                        <span class="text-xs text-slate-500">{{ $last->created_at->diffForHumans() }}</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-400 truncate mt-0.5">
                                    {{ $last ? Str::limit($last->body, 60) : 'No messages yet' }}
                                </p>
                            </div>
                            @if($client->unread_count > 0)
                                <span class="bg-amber-600 text-white text-xs rounded-full px-2 py-0.5 font-medium shrink-0">{{ $client->unread_count }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
                <div class="mt-3 text-right">
                    <a href="{{ route('clients.index') }}" class="text-xs text-slate-400 hover:text-slate-300">Manage clients →</a>
                </div>
            @endif

        </div>

    </div>
</x-app-layout>
