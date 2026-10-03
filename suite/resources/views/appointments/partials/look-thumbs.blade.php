{{--
    Staff-side thumbnail grid for a booking's look photos (private files,
    streamed via appointments.inspiration.show).

    @param \Illuminate\Support\Collection $photos
    @param string $label        alt-text prefix, e.g. "Inspiration photo"
    @param bool   $removable    show a remove button (only for staff "after" photos)
--}}
<div class="grid grid-cols-3 gap-2">
    @foreach($photos as $photo)
        <div class="relative">
            <a href="{{ $photo->staffUrl() }}" target="_blank" rel="noopener"
               class="block rounded-lg overflow-hidden border border-slate-700 hover:border-[#0078D4] transition-colors">
                <img src="{{ $photo->staffUrl('thumb') }}" alt="{{ $label }} {{ $loop->iteration }}"
                     loading="lazy" class="w-full aspect-square object-cover">
            </a>
            @if($removable ?? false)
                <form method="POST" action="{{ route('appointments.look.results.destroy', [$photo->appointment_id, $photo]) }}"
                      onsubmit="return confirm('Remove this after photo?')" class="absolute top-1 right-1">
                    @csrf @method('DELETE')
                    <button class="w-7 h-7 rounded-full bg-slate-900 hover:bg-slate-700 text-white flex items-center justify-center" aria-label="Remove after photo">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </form>
            @endif
        </div>
    @endforeach
</div>
