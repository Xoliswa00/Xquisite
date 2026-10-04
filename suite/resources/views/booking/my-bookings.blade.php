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
                    $inspoSlots   = \App\Services\Booking\InspirationPhotoService::MAX_PER_APPOINTMENT - $inspoPhotos->count();
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
                                        class="text-xs text-[#0078D4] hover:text-[#0065B8] font-medium">
                                    {{ $inspoPhotos->isEmpty() ? 'Add the look you want' : 'Add more' }}
                                </button>
                            @endif
                        </div>

                        @if($inspoPhotos->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach($inspoPhotos as $photo)
                                    <div class="relative">
                                        <a href="{{ $photo->customerUrl($slug) }}" target="_blank" rel="noopener">
                                            <img src="{{ $photo->customerUrl($slug, 'thumb') }}" alt="Inspiration photo {{ $loop->iteration }}"
                                                 class="w-20 h-20 object-cover rounded-xl border border-slate-200">
                                        </a>
                                        @if($appt->inspirationIsEditable())
                                            <form method="POST" action="{{ route('book.inspiration.destroy', [$slug, $appt, $photo]) }}"
                                                  onsubmit="return confirm('Remove this photo?')"
                                                  class="absolute top-1 right-1">
                                                @csrf @method('DELETE')
                                                <button class="w-6 h-6 rounded-full bg-slate-900 hover:bg-slate-700 text-white flex items-center justify-center" aria-label="Remove photo">
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
            @php $lookPhotos = $look->resultPhotos->concat($look->inspirationPhotos)->take(3); @endphp
            <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-slate-900 truncate">{{ $look->services->pluck('name')->join(', ') }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $look->scheduled_at->format('d M Y') }}</p>
                    </div>
                    <form method="POST" action="{{ route('book.looks.forget', [$slug, $look]) }}"
                          onsubmit="return confirm('Remove this saved look? The photos will be deleted after 90 days.')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-slate-400 hover:text-red-500 shrink-0">Remove</button>
                    </form>
                </div>
                @if($lookPhotos->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($lookPhotos as $photo)
                            <a href="{{ $photo->customerUrl($slug) }}" target="_blank" rel="noopener">
                                <img src="{{ $photo->customerUrl($slug, 'thumb') }}" alt="Saved look photo {{ $loop->iteration }}"
                                     class="w-20 h-20 object-cover rounded-xl border border-slate-200">
                            </a>
                        @endforeach
                    </div>
                @endif
                @if($look->inspiration_notes)
                    <p class="text-xs text-slate-500 whitespace-pre-line">{{ $look->inspiration_notes }}</p>
                @endif
                <a href="{{ route('book.looks.rebook', [$slug, $look]) }}"
                   class="inline-flex px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-xs font-semibold rounded-lg">
                    Book this look again
                </a>
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

</div>
@endsection
