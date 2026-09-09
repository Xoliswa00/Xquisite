{{-- One service card for the gallery/grid view. Expects: $service, $maxPhotos --}}
@php $count = $service->photos_count; @endphp
<a href="{{ route('services.edit', $service) }}#photos"
   class="group block rounded-xl border border-slate-700 bg-slate-800 overflow-hidden hover:border-slate-500 transition-colors">

    <div class="relative aspect-[4/3] bg-slate-900">
        @if($service->coverPhoto)
            <img src="{{ $service->coverPhoto->thumbUrl() }}" alt="" loading="lazy"
                 width="600" height="450"
                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]">
            @if($count > 1)
                <span class="absolute bottom-1.5 right-1.5 bg-black/60 text-white text-[10px] font-semibold px-1.5 py-0.5 rounded">
                    {{ $count }} photos
                </span>
            @endif
        @else
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-1.5 text-slate-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                <span class="text-[11px] font-medium text-amber-300/80">Add photos</span>
            </div>
        @endif
    </div>

    <div class="p-2.5">
        <p class="text-xs font-medium text-white truncate group-hover:text-[#B8D4F0] transition-colors">{{ $service->name }}</p>
        <p class="text-[11px] text-slate-500 truncate mt-0.5">
            @if($service->category){{ $service->category->name }} · @endif{{ $count }}/{{ $maxPhotos }}
        </p>
    </div>
</a>
