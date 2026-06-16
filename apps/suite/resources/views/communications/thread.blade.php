<x-app-layout>
    <x-slot name="header">Messages — {{ $client->name }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-5">
        <div class="flex items-center gap-3">
            <a href="{{ route('clients.show', $client) }}" class="text-slate-400 hover:text-white text-sm">← {{ $client->name }}</a>
        </div>

        {{-- Chat thread --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 min-h-64 max-h-[60vh] overflow-y-auto">
            @forelse($messages as $msg)
                @if($msg->is_from_owner)
                    {{-- Owner (right, dark) --}}
                    <div class="flex justify-end">
                        <div class="max-w-[75%]">
                            @if($msg->subject)
                                <p class="text-xs text-slate-400 text-right mb-1">{{ $msg->subject }}</p>
                            @endif
                            <div class="bg-slate-800 text-white rounded-2xl rounded-tr-none px-4 py-3 text-sm">
                                {!! nl2br(e($msg->body)) !!}
                            </div>
                            <p class="text-[10px] text-slate-500 text-right mt-1">{{ $msg->created_at->diffForHumans() }} · {{ $msg->fromUser?->name ?? 'You' }}</p>
                        </div>
                    </div>
                @else
                    {{-- Client (left, white) --}}
                    <div class="flex justify-start">
                        <div class="max-w-[75%]">
                            <div class="bg-white text-slate-900 border border-slate-200 rounded-2xl rounded-tl-none px-4 py-3 text-sm">
                                {!! nl2br(e($msg->body)) !!}
                            </div>
                            <p class="text-[10px] text-slate-500 mt-1">{{ $msg->created_at->diffForHumans() }} · {{ $client->name }}</p>
                        </div>
                    </div>
                @endif
            @empty
                <p class="text-center text-sm text-slate-500 py-8">No messages yet. Start the conversation below.</p>
            @endforelse
        </div>

        {{-- Compose --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5">
            <form method="POST" action="{{ route('clients.messages.store', $client) }}" class="space-y-3">
                @csrf
                <div>
                    <x-input-label value="Subject (optional)" />
                    <x-text-input name="subject" class="mt-1 w-full" placeholder="Re: your booking" />
                </div>
                <div>
                    <x-input-label value="Message" />
                    <textarea name="body" rows="4" required class="mt-1 w-full rounded-lg bg-slate-800 border-slate-700 text-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Type your message…"></textarea>
                    <x-input-error :messages="$errors->get('body')" />
                </div>
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg">Send Message</button>
            </form>
        </div>
    </div>
</x-app-layout>
