<x-app-layout>
<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-6">

    <div class="border-b border-slate-200 pb-6">
        <nav class="flex mb-2">
            <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                <li><a href="{{ route('portal.dashboard') }}" class="hover:text-slate-700">Portal</a></li>
                <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                <li class="text-slate-900">Messages</li>
            </ol>
        </nav>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Messages</h2>
        <p class="text-slate-400 text-sm mt-1">Your conversation with {{ $client->company->name ?? 'your service provider' }}.</p>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    {{-- MESSAGE THREAD --}}
    <div class="space-y-4">
        @forelse($messages as $message)
        @php $isOwner = $message->is_from_owner; @endphp
        <div class="flex {{ $isOwner ? 'justify-start' : 'justify-end' }}">
            <div class="max-w-[80%] {{ $isOwner ? 'order-2' : 'order-1' }}">

                {{-- BUBBLE --}}
                <div class="rounded-2xl px-4 py-3 shadow-sm
                    {{ $isOwner
                        ? 'bg-white border border-slate-200 rounded-tl-none'
                        : 'bg-slate-900 text-white rounded-tr-none' }}">

                    @if($message->subject)
                    <p class="text-[10px] font-black uppercase tracking-widest {{ $isOwner ? 'text-slate-400' : 'text-slate-400' }} mb-1">
                        {{ $message->subject }}
                    </p>
                    @endif

                    <p class="text-sm leading-relaxed {{ $isOwner ? 'text-slate-800' : 'text-white' }}">
                        {{ $message->body }}
                    </p>
                </div>

                {{-- META --}}
                <div class="flex items-center gap-2 mt-1 {{ $isOwner ? 'justify-start pl-1' : 'justify-end pr-1' }}">
                    <span class="text-[10px] font-bold text-slate-400">
                        {{ $message->fromUser->name ?? ($isOwner ? 'Provider' : 'You') }}
                    </span>
                    <span class="text-[10px] text-slate-300">·</span>
                    <span class="text-[10px] text-slate-400">{{ $message->created_at->diffForHumans() }}</span>
                    @if(!$isOwner && $message->read_at)
                        <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <div class="w-14 h-14 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
            </div>
            <p class="text-slate-400 text-sm font-bold">No messages yet</p>
            <p class="text-slate-400 text-xs mt-1">Messages from your provider will appear here.</p>
        </div>
        @endforelse
    </div>

    {{-- REPLY FORM --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Send a Reply</h3>
        <form method="POST" action="{{ route('portal.messages.reply') }}" class="space-y-3">
            @csrf
            <textarea name="body" rows="4" required
                      class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 resize-none"
                      placeholder="Type your message here...">{{ old('body') }}</textarea>
            @error('body')
                <p class="text-xs text-rose-500">{{ $message }}</p>
            @enderror
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-slate-900 hover:bg-amber-400 hover:text-slate-900 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition-all duration-300 shadow-lg shadow-slate-200">
                    Send Message
                </button>
            </div>
        </form>
    </div>

</div>
</x-app-layout>
