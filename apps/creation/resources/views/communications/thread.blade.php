<x-app-layout>
<div class="max-w-3xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-6">

    <div class="border-b border-slate-200 pb-6">
        <nav class="flex mb-2">
            <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                <li><a href="{{ route('clients.index') }}" class="hover:text-slate-700">Clients</a></li>
                <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                <li class="text-slate-900">{{ $client->name }}</li>
                <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                <li>Messages</li>
            </ol>
        </nav>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">{{ $client->name }}</h2>
                <p class="text-slate-400 text-sm mt-0.5">{{ $client->email }}</p>
            </div>
            <div class="flex items-center gap-2">
                @php $unread = $messages->where('is_from_owner', false)->whereNull('read_at')->count(); @endphp
                @if($unread > 0)
                <span class="text-xs font-black bg-amber-100 text-amber-700 border border-amber-200 px-3 py-1 rounded-full">
                    {{ $unread }} unread
                </span>
                @endif
            </div>
        </div>
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
        <div class="flex {{ $isOwner ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[80%]">
                <div class="rounded-2xl px-4 py-3 shadow-sm
                    {{ $isOwner
                        ? 'bg-slate-900 text-white rounded-tr-none'
                        : 'bg-white border border-slate-200 rounded-tl-none' }}">

                    @if($message->subject)
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">
                        {{ $message->subject }}
                    </p>
                    @endif

                    <p class="text-sm leading-relaxed {{ $isOwner ? 'text-white' : 'text-slate-800' }}">
                        {{ $message->body }}
                    </p>
                </div>
                <div class="flex items-center gap-2 mt-1 {{ $isOwner ? 'justify-end pr-1' : 'justify-start pl-1' }}">
                    <span class="text-[10px] font-bold text-slate-400">{{ $isOwner ? 'You' : $client->name }}</span>
                    <span class="text-[10px] text-slate-300">·</span>
                    <span class="text-[10px] text-slate-400">{{ $message->created_at->diffForHumans() }}</span>
                    @if($isOwner && $message->read_at)
                        <svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <p class="text-slate-400 text-sm">No messages yet. Start the conversation below.</p>
        </div>
        @endforelse
    </div>

    {{-- COMPOSE FORM (OWNER) --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-5">
        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 mb-4">Send Message to {{ $client->name }}</h3>
        <form method="POST" action="{{ route('communications.store', $client) }}" class="space-y-3">
            @csrf
            <div>
                <input type="text" name="subject"
                       value="{{ old('subject') }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                       placeholder="Subject (optional)">
            </div>
            <div>
                <textarea name="body" rows="4" required
                          class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 resize-none"
                          placeholder="Your message...">{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end">
                <button type="submit"
                        class="bg-slate-900 hover:bg-amber-400 hover:text-slate-900 text-white font-bold px-6 py-2.5 rounded-xl text-sm transition-all duration-300 shadow-lg shadow-slate-200">
                    Send to {{ $client->name }}
                </button>
            </div>
        </form>
    </div>

</div>
</x-app-layout>
