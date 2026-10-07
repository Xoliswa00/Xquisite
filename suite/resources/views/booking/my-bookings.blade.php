@extends('layouts.booking')

@section('content')
<div class="space-y-8">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">My Bookings</h1>
            <p class="text-slate-500 text-sm mt-0.5">{{ auth('customer')->user()->name }}</p>
        </div>
        <a href="{{ route('book.index', $slug) }}"
           class="bg-[#0078D4] hover:bg-[#0065B8] text-white px-4 py-2 rounded-xl text-sm font-semibold transition">
            + Book again
        </a>
    </div>

    {{-- Upcoming --}}
    <div>
        <h2 class="text-base font-semibold text-slate-700 mb-3">Upcoming</h2>

        @if($upcoming->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-400">
                No upcoming appointments.
                <a href="{{ route('book.index', $slug) }}" class="text-[#0078D4] hover:underline ml-1">Book one now &rarr;</a>
            </div>
        @else
            <div class="space-y-3">
                @foreach($upcoming as $appt)
                @php
                    $serviceNames = $appt->services->pluck('name')->join(', ');
                    $inspoPhotos  = $appt->inspirationPhotos;
                    $inspoOpen    = $appt->services->contains(fn($s) => $s->accepts_inspiration_photos) || $inspoPhotos->isNotEmpty();
                    $inspoSlots   = \App\Services\Booking\LookPhotoService::MAX_PER_APPOINTMENT - $inspoPhotos->count();
                    $inspoErrored = old('inspiration_for') == $appt->id;
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3"
                     x-data="{ showUpload: false, showInspo: {{ $inspoErrored ? 'true' : 'false' }}, inspoBusy: false }">

                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 truncate">{{ $serviceNames }}</p>
                            <p class="text-sm text-slate-500 mt-0.5">
                                with {{ $appt->staff->name }}
                                &middot; {{ $appt->scheduled_at->format('d M Y, H:i') }}
                                &middot; {{ $appt->duration_minutes }} min
                            </p>
                            @if($appt->payment_proof_path)
                                <p class="mt-1 flex items-center gap-1 text-xs text-emerald-600 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Proof of payment submitted
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-col items-start sm:items-end gap-2 shrink-0">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $appt->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                            </span>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                @if(!$appt->payment_proof_path)
                                    <button type="button" @click="showUpload = !showUpload"
                                            class="text-xs text-[#0078D4] hover:text-[#0065B8] font-medium">
                                        Upload proof of payment
                                    </button>
                                @endif
                                @if($appt->scheduled_at->diffInHours(now(), false) < -2)
                                    <form method="POST" action="{{ route('book.cancel', [$slug, $appt]) }}"
                                          onsubmit="return confirm('Cancel this appointment?')">
                                        @csrf @method('PATCH')
                                        <button class="text-xs text-red-500 hover:text-red-700">Cancel</button>
                                    </form>
                                    <a href="{{ route('book.edit', [$slug, $appt]) }}"
                                       class="text-xs text-[#0078D4] hover:text-[#0065B8]">Reschedule &rarr;</a>
                                @else
                                    <span class="text-xs text-slate-300" title="Cancellation window has passed">Cannot cancel</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Quote from your photos --}}
                    @if($appt->quote_status === 'requested')
                        <div class="flex items-start gap-2 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2.5 text-xs text-amber-800">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Waiting for {{ $tenant->name }} to send your price and time. Your slot is held.</span>
                        </div>
                    @elseif($appt->quote_status === 'sent')
                        <div class="bg-[#F0F7FF] border border-[#DCEEFA] rounded-xl p-4 space-y-3">
                            <div>
                                <p class="text-sm font-semibold text-[#002B5B]">Your quote is ready</p>
                                <p class="text-lg font-bold text-[#002B5B] mt-1">
                                    R{{ number_format((float) $appt->quoted_price, 2) }}
                                    <span class="text-sm font-medium text-slate-600">&middot; about {{ \App\Services\Notifications\BookingNotificationService::humanMinutes((int) $appt->quoted_duration_minutes) }}</span>
                                </p>
                                @if($appt->quote_note)
                                    <p class="text-xs text-slate-600 mt-1 whitespace-pre-line">{{ $appt->quote_note }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <form method="POST" action="{{ route('book.quote.accept', [$slug, $appt]) }}">
                                    @csrf
                                    <button class="px-4 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-xs font-semibold rounded-lg">Accept quote</button>
                                </form>
                                <form method="POST" action="{{ route('book.quote.decline', [$slug, $appt]) }}"
                                      onsubmit="return confirm('Decline this quote? Your booking will be cancelled, with nothing to pay.')">
                                    @csrf
                                    <button class="px-3 py-2.5 text-xs font-medium text-slate-500 hover:text-red-500">Decline</button>
                                </form>
                            </div>
                        </div>
                    @elseif($appt->quote_status === 'accepted')
                        <p class="text-xs text-emerald-700">
                            Quote accepted: R{{ number_format((float) $appt->quoted_price, 2) }} &middot; about {{ \App\Services\Notifications\BookingNotificationService::humanMinutes((int) $appt->quoted_duration_minutes) }}
                        </p>
                    @endif
                    @error('quote')<p class="text-xs text-red-500">{{ $message }}</p>@enderror

                    {{-- Inline proof upload --}}
                    <div x-show="showUpload" x-cloak x-transition class="border-t border-slate-100 pt-3">
                        <form method="POST"
                              action="{{ route('book.payment-proof', [$slug, $appt]) }}"
                              enctype="multipart/form-data"
                              class="flex items-center gap-3 flex-wrap">
                            @csrf
                            @if($errors->has('payment_proof'))
                                <p class="w-full text-xs text-red-500">{{ $errors->first('payment_proof') }}</p>
                            @endif
                            <input type="file" name="payment_proof" accept=".pdf,.jpg,.jpeg,.png,.webp"
                                   class="flex-1 text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                            <button type="submit"
                                    class="px-4 py-1.5 bg-[#0078D4] text-white text-xs font-semibold rounded-lg whitespace-nowrap">
                                Upload
                            </button>
                        </form>
                        <p class="text-xs text-slate-400 mt-1">PDF, JPG, PNG or WebP &middot; max 8 MB</p>
                    </div>

                    {{-- Inspiration photos --}}
                    @if($inspoOpen)
                    <div class="border-t border-slate-100 pt-3 space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-xs font-semibold text-slate-500">
                                Inspiration photos
                                @if($inspoPhotos->isNotEmpty())<span class="font-normal text-slate-400">&middot; {{ $inspoPhotos->count() }}</span>@endif
                            </p>
                            @if($inspoSlots > 0 && $appt->inspirationIsEditable())
                                <button type="button" @click="showInspo = !showInspo"
                                        class="text-xs text-[#0078D4] hover:text-[#0065B8] font-medium px-2 py-2">
                                    {{ $inspoPhotos->isEmpty() ? 'Add the look you want' : 'Add more' }}
                                </button>
                            @endif
                        </div>

                        @if($inspoPhotos->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach($inspoPhotos as $photo)
                                    <div class="relative">
                                        <a href="{{ $photo->customerUrl($slug) }}" target="_blank" rel="noopener">
                                            <img src="{{ $photo->customerUrl($slug, 'thumb') }}" alt="Inspiration photo {{ $loop->iteration }}" loading="lazy"
                                                 class="w-20 h-20 object-cover rounded-xl border border-slate-200">
                                        </a>
                                        @if($appt->inspirationIsEditable())
                                            <form method="POST" action="{{ route('book.inspiration.destroy', [$slug, $appt, $photo]) }}"
                                                  onsubmit="return confirm('Remove this photo?')"
                                                  class="absolute top-0 right-0 p-1">
                                                @csrf @method('DELETE')
                                                <button class="w-8 h-8 rounded-full bg-slate-900 hover:bg-slate-700 text-white flex items-center justify-center" aria-label="Remove photo">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($appt->inspiration_notes)
                            <p class="text-xs text-slate-500 whitespace-pre-line">{{ $appt->inspiration_notes }}</p>
                        @endif

                        @if($inspoSlots > 0 && $appt->inspirationIsEditable())
                        <form method="POST" action="{{ route('book.inspiration.store', [$slug, $appt]) }}"
                              enctype="multipart/form-data"
                              x-show="showInspo" x-cloak x-transition
                              @submit="if (inspoBusy) $event.preventDefault()"
                              class="space-y-3">
                            @csrf
                            <input type="hidden" name="inspiration_for" value="{{ $appt->id }}">
                            @if($inspoErrored)
                                @if($errors->has('inspiration_photos'))
                                    <p class="text-xs text-red-500">{{ $errors->first('inspiration_photos') }}</p>
                                @endif
                                @foreach($errors->get('inspiration_photos.*') as $messages)
                                    <p class="text-xs text-red-500">{{ $messages[0] }}</p>
                                @endforeach
                            @endif

                            @include('booking.partials.inspiration-picker', ['max' => $inspoSlots, 'inputId' => 'inspiration-photos-' . $appt->id])

                            @if(! $appt->inspiration_notes)
                                <textarea name="inspiration_notes" rows="2" maxlength="1000"
                                          placeholder="Describe the look (optional)"
                                          class="w-full border-slate-300 rounded-xl text-sm"></textarea>
                            @endif

                            <button type="submit" :disabled="inspoBusy"
                                    class="px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] disabled:opacity-60 text-white text-xs font-semibold rounded-lg">
                                Save photos
                            </button>
                        </form>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Saved looks: bookings the business saved as this client's look --}}
    @if($savedLooks->isNotEmpty())
    <div>
        <h2 class="text-base font-semibold text-slate-700 mb-1">Your saved looks</h2>
        <p class="text-xs text-slate-400 mb-3">Looks {{ $tenant->name }} saved for you. Book one again and they'll see it before you arrive.</p>
        <div class="space-y-3">
            @foreach($savedLooks as $look)
            @php
                $lookPhotos = $look->orderedLookPhotos()->take(3);
                $lookTitle  = $look->services->pluck('name')->join(', ') ?: 'Your look from ' . $look->scheduled_at->format('d M Y');
            @endphp
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900 truncate">{{ $lookTitle }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $look->scheduled_at->format('d M Y') }}</p>
                    </div>
                    <form method="POST" action="{{ route('book.looks.forget', [$slug, $look]) }}"
                          onsubmit="return confirm(@js("Remove this look? {$tenant->name} won't see it any more, and their after photos of you are deleted now."))">
                        @csrf @method('DELETE')
                        <button class="text-xs text-slate-400 hover:text-red-500 shrink-0 px-2 py-2 -mt-2">Remove</button>
                    </form>
                </div>
                @if($lookPhotos->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($lookPhotos as $photo)
                            <a href="{{ $photo->customerUrl($slug) }}" target="_blank" rel="noopener" class="relative block">
                                <img src="{{ $photo->customerUrl($slug, 'thumb') }}"
                                     alt="{{ $photo->isResult() ? 'How it turned out' : 'Your inspiration' }}, photo {{ $loop->iteration }}"
                                     loading="lazy" class="block w-20 h-20 object-cover rounded-xl border border-slate-200">
                                <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded-md bg-slate-900 text-white text-[10px] font-medium">
                                    {{ $photo->isResult() ? 'After' : 'Yours' }}
                                </span>
                                <span class="sr-only">(opens full size in a new tab)</span>
                            </a>
                        @endforeach
                    </div>
                @endif
                @if($look->inspiration_notes)
                    <p class="text-xs text-slate-500 whitespace-pre-line">{{ $look->inspiration_notes }}</p>
                @endif
                <form method="POST" action="{{ route('book.looks.showcase', [$slug, $look]) }}"
                      class="flex items-start gap-3 bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5">
                    @csrf
                    <input type="hidden" name="allow" value="{{ $look->look_showcase_at ? 0 : 1 }}">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-slate-700">
                            {{ $look->look_showcase_at ? "You're happy for {$tenant->name} to share this look" : "Happy for {$tenant->name} to share this look?" }}
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">For example on Instagram or in their portfolio. You can change your mind any time.</p>
                    </div>
                    <button class="shrink-0 px-3 py-2 rounded-lg text-xs font-semibold {{ $look->look_showcase_at ? 'text-slate-600 border border-slate-300 hover:bg-white' : 'bg-slate-900 hover:bg-slate-700 text-white' }}">
                        {{ $look->look_showcase_at ? 'Stop sharing' : 'Yes, share it' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('book.looks.rebook', [$slug, $look]) }}">
                    @csrf
                    <button class="inline-flex px-4 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-xs font-semibold rounded-lg">
                        Book this look again
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Past --}}
    @if($past->isNotEmpty())
    <div>
        <h2 class="text-base font-semibold text-slate-700 mb-3">Past</h2>
        <div class="space-y-2">
            @foreach($past as $appt)
            @php $serviceNames = $appt->services->pluck('name')->join(', '); @endphp
            <div class="bg-white rounded-2xl border border-slate-200 p-4 flex items-center justify-between opacity-70">
                <div class="min-w-0">
                    <p class="font-semibold text-slate-900 truncate">{{ $serviceNames }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">
                        with {{ $appt->staff->name }}
                        &middot; {{ $appt->scheduled_at->format('d M Y, H:i') }}
                    </p>
                </div>
                <span class="ml-4 shrink-0 px-2 py-0.5 rounded-full text-xs
                    @if($appt->status === 'completed') bg-slate-100 text-slate-600
                    @elseif($appt->status === 'cancelled') bg-red-100 text-red-600
                    @else bg-slate-100 text-slate-500 @endif">
                    {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif


    {{-- "Time for your next one?" reminders: the client can switch them off (or back on) here or from any email --}}
    @php $remindersOn = auth('customer')->user()->rebook_reminders_opt_out_at === null; @endphp
    <form method="POST" action="{{ route('book.rebook-reminders.toggle', $slug) }}"
          class="flex items-center justify-between gap-3 bg-white rounded-2xl border border-slate-200 px-5 py-4">
        @csrf
        <input type="hidden" name="on" value="{{ $remindersOn ? 0 : 1 }}">
        <div class="min-w-0">
            <p class="text-sm font-medium text-slate-700">Rebook reminders are {{ $remindersOn ? 'on' : 'off' }}</p>
            <p class="text-xs text-slate-500 mt-0.5">One message when it's usually time for your next visit. Appointment reminders aren't affected.</p>
        </div>
        <button class="shrink-0 px-3 py-2 text-xs font-semibold rounded-lg border border-slate-300 text-slate-600 hover:bg-slate-50">
            {{ $remindersOn ? 'Turn off' : 'Turn on' }}
        </button>
    </form>

</div>
@endsection
