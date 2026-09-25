{{-- One standard message to an applicant: read it, send it (WhatsApp or email), record that it went. --}}
@php
    $message = \App\Services\FoundingTwentyMessages::for($type, $a);
    $sentAt = $a->{\App\Services\FoundingTwentyMessages::COLUMNS[$type]};
@endphp
<div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm">
    <x-whatsapp-link :phone="$a->phone" :message="$message['body']" class="text-slate-300">WhatsApp</x-whatsapp-link>
    @if($a->email)
        <form method="POST" action="{{ route('admin.founding-twenty.message.email', [$a, $type]) }}">
            @csrf
            <button type="submit" class="text-[#0078D4] hover:underline">Email it</button>
        </form>
    @endif
    @unless($sentAt)
        <form method="POST" action="{{ route('admin.founding-twenty.message.told', [$a, $type]) }}">
            @csrf
            <button type="submit" class="text-slate-400 hover:text-white underline">Mark as told</button>
        </form>
    @endunless
    @if($sentAt)
        <span class="text-xs text-emerald-400">Sent {{ $sentAt->diffForHumans() }}</span>
    @endif
</div>
