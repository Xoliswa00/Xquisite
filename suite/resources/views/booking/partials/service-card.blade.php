{{--
    One selectable service card for the public booking portal.
    Rendered inside the servicePicker() Alpine scope, so it can reference
    `selected`, `toggle()` and `openGallery()` directly.

    Expects: $service, $dotClass (Tailwind bg-* class for the accent stripe)
--}}
@php
    $desc      = $service->description ?? '';
    $descLines = $desc ? array_values(array_filter(array_map('trim', explode("\n", $desc)))) : [];
    $isList    = count($descLines) > 1 && collect($descLines)->every(fn($l) => preg_match('/^[-*•·]|\d+[.)]\s/', $l));
    $listItems = $isList ? array_map(fn($l) => trim(preg_replace('/^[-*•·]\s*|\d+[.)]\s*/', '', $l)), $descLines) : [];
    $isLong    = $desc && (strlen($desc) > 100 || count($descLines) > 2);
    $needsMore = $isLong || count($listItems) > 2;
    $photos    = $service->visiblePhotos;
    $cover     = $service->display_photo;
@endphp
<div role="button" tabindex="0"
     @click="toggle({{ $service->id }})"
     @keydown.enter.stop="toggle({{ $service->id }})"
     @keydown.space.prevent.stop="toggle({{ $service->id }})"
     class="group relative flex flex-col text-left w-full self-start rounded-2xl border bg-white cursor-pointer transition-all duration-200 overflow-hidden select-none"
     :class="selected.includes({{ $service->id }})
         ? 'border-[#0078D4] bg-[#F0F7FF]/80 ring-2 ring-[#0078D4] ring-offset-1 shadow-lg shadow-[#E8F2FA]'
         : 'border-slate-200 hover:border-slate-300 hover:shadow-md'">

    {{-- Left accent stripe --}}
    <div class="absolute top-0 left-0 w-1 h-full {{ $dotClass }} transition-opacity z-20"
         :class="selected.includes({{ $service->id }}) ? 'opacity-100' : 'opacity-40'"></div>

    {{-- Cover photo. Tapping the image still selects the service; the pill opens the gallery. --}}
    @if($cover)
        <div class="relative w-full aspect-[16/10] bg-slate-100 overflow-hidden">
            <img src="{{ $cover->thumbUrl() }}" alt="{{ $cover->alt_text ?: $service->name }}" loading="lazy"
                 width="600" height="375" decoding="async"
                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                 onerror="this.closest('div').remove()">
            <span class="pointer-events-none absolute inset-x-0 bottom-0 h-12 bg-gradient-to-t from-black/35 to-transparent"></span>
            <button type="button"
                    @click.stop="openGallery({{ $service->id }})"
                    class="absolute bottom-2 left-2 flex items-center gap-1.5 bg-black/55 hover:bg-black/75 text-white text-[11px] font-semibold pl-2 pr-2.5 py-1 rounded-full transition-colors"
                    aria-label="View {{ $photos->count() }} {{ Str::plural('photo', $photos->count()) }} of {{ $service->name }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4zM4 15l4-4 4 4m-2-2l3-3 5 5"/></svg>
                {{ $photos->count() > 1 ? $photos->count() . ' photos' : 'View photo' }}
            </button>
        </div>
    @endif

    {{-- Check ring (selected) --}}
    <div x-show="selected.includes({{ $service->id }})" x-cloak
         class="absolute top-3.5 right-3.5 z-20 w-6 h-6 bg-[#0078D4] rounded-full flex items-center justify-center shadow-md ring-2 ring-white">
        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    {{-- Plus ring (unselected) --}}
    <div x-show="!selected.includes({{ $service->id }})"
         class="absolute top-3.5 right-3.5 z-20 w-6 h-6 bg-white/90 group-hover:bg-[#E8F2FA] rounded-full flex items-center justify-center border border-slate-300 group-hover:border-[#B8D4F0] shadow-sm transition-all">
        <svg class="w-3 h-3 text-slate-400 group-hover:text-[#0078D4]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
        </svg>
    </div>

    <div class="pl-3 {{ $cover ? 'pr-4 pt-3.5' : 'pr-10 pt-4' }} pb-4 flex flex-col flex-1">
        <p class="font-semibold text-sm text-slate-900 group-hover:text-[#002B5B] transition-colors leading-snug"
           :class="selected.includes({{ $service->id }}) ? '!text-[#002B5B]' : ''">
            {{ $service->name }}
        </p>
        @if($desc)
        <div x-data="{ open: false }" class="mt-1">
            {{-- Collapsed --}}
            <div x-show="!open">
                @if($isList)
                    <ul class="text-xs text-slate-400 leading-relaxed space-y-0.5 list-disc list-inside">
                        @foreach(array_slice($listItems, 0, 2) as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                        @if(count($listItems) > 2)
                            <li class="list-none pl-4 text-slate-300">+{{ count($listItems) - 2 }} more…</li>
                        @endif
                    </ul>
                @else
                    <p class="text-xs text-slate-400 leading-relaxed line-clamp-2">{{ $desc }}</p>
                @endif
            </div>
            {{-- Expanded --}}
            <div x-show="open" x-cloak>
                @if($isList)
                    <ul class="text-xs text-slate-400 leading-relaxed space-y-0.5 list-disc list-inside">
                        @foreach($listItems as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-xs text-slate-400 leading-relaxed whitespace-pre-line">{{ $desc }}</p>
                @endif
            </div>
            @if($needsMore)
            <button type="button" @click.stop="open = !open"
                    class="mt-1 flex items-center gap-1 text-xs text-[#0078D4] font-medium hover:underline focus:outline-none">
                <span x-text="open ? 'Show less' : 'Read more'"></span>
                <svg class="w-3 h-3 transition-transform duration-200" :class="open ? '-rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            @endif
        </div>
        @endif
        <div class="flex items-center justify-between mt-auto pt-3.5">
            <div class="flex items-center gap-1.5 text-xs text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $service->duration_minutes }} min
            </div>
            <p class="font-bold text-slate-900 text-sm"
               :class="selected.includes({{ $service->id }}) ? '!text-[#002B5B]' : ''">
                {{ $service->priceLabel() }}
            </p>
        </div>
        @if($service->pricing_type !== 'flat')
        <div x-show="selected.includes({{ $service->id }})" x-cloak
             @click.stop class="flex items-center justify-between mt-2.5 pt-2.5 border-t border-slate-100">
            <span class="text-xs text-slate-500">{{ $service->unit_label ?? ($service->pricing_type === 'per_head' ? 'guests' : 'units') }}</span>
            <div class="flex items-center gap-2">
                <button type="button" @click="setQty({{ $service->id }}, (quantities[{{ $service->id }}] || 1) - 1)"
                        class="w-6 h-6 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold">&minus;</button>
                <span class="w-5 text-center text-sm font-semibold text-slate-900" x-text="quantities[{{ $service->id }}] || 1"></span>
                <button type="button" @click="setQty({{ $service->id }}, (quantities[{{ $service->id }}] || 1) + 1)"
                        class="w-6 h-6 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold">+</button>
            </div>
        </div>
        @endif
    </div>
</div>
