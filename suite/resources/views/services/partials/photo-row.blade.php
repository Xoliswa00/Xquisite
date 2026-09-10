{{-- One service row for the Service Photos manager. Expects: $service, $maxPhotos --}}
@php $count = $service->photos_count; @endphp
<div class="flex items-center gap-3 sm:gap-4 p-3 sm:p-4 hover:bg-slate-700/40 transition-colors">

    <div class="shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden bg-slate-900 border border-slate-700 flex items-center justify-center">
        @if($service->coverPhoto)
            <img src="{{ $service->coverPhoto->thumbUrl() }}" alt="" loading="lazy"
                 width="64" height="64" class="w-full h-full object-cover">
        @else
            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v12a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6a1.5 1.5 0 011.5-1.5z"/>
            </svg>
        @endif
    </div>

    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-white truncate">{{ $service->name }}</p>
        <div class="flex items-center gap-2 mt-0.5">
            @if($service->category && ! ($hideCategory ?? false))
                <span class="text-xs text-slate-500">{{ $service->category->icon }} {{ $service->category->name }}</span>
            @endif
            @unless($service->is_active)
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-700 text-slate-400">Inactive</span>
            @endunless
        </div>
    </div>

    <div class="shrink-0 text-right hidden sm:block">
        @if($count === 0)
            <span class="text-xs font-medium text-amber-300/90 whitespace-nowrap">No photos yet</span>
        @else
            <span class="text-xs text-slate-400 whitespace-nowrap">{{ $count }} / {{ $maxPhotos }} photos</span>
        @endif
    </div>

    <a href="{{ route('services.edit', $service) }}#photos"
       class="shrink-0 text-xs font-semibold px-3 py-2 rounded-lg transition-colors whitespace-nowrap
              {{ $count === 0 ? 'bg-[#0078D4] hover:bg-[#0065B8] text-white' : 'bg-slate-700 hover:bg-slate-600 text-slate-200' }}">
        {{ $count === 0 ? 'Add photos' : 'Manage' }}
    </a>
</div>
