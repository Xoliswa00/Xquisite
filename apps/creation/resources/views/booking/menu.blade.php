<x-app-layout>
<div class="min-h-screen bg-slate-50">

    {{-- HERO HEADER --}}
    <div class="bg-slate-900 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-slate-400 mb-2">Our Services</p>
                    <h1 class="text-4xl font-black tracking-tighter">{{ $company->name }}</h1>
                    @if($company->phone || $company->email)
                    <p class="text-slate-400 text-sm mt-2 space-x-4">
                        @if($company->phone)<span>📞 {{ $company->phone }}</span>@endif
                        @if($company->email)<span>✉️ {{ $company->email }}</span>@endif
                    </p>
                    @endif
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('service-categories.index') }}"
                       class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-white border border-slate-700 hover:border-slate-500 px-4 py-2 rounded-xl transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Manage Categories
                    </a>
                </div>
            </div>

            {{-- CATEGORY FILTER TABS --}}
            @if($categories->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2">
                <a href="{{ route('booking.menu') }}"
                   class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider transition-all
                          {{ !$activeFilter ? 'bg-white text-slate-900' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                    All Services
                </a>
                @foreach($categories as $cat)
                @php $c = \App\Models\ServiceCategory::colorClasses()[$cat->color] ?? \App\Models\ServiceCategory::colorClasses()['slate']; @endphp
                <a href="{{ route('booking.menu', ['category' => $cat->id]) }}"
                   class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider transition-all
                          {{ $activeFilter == $cat->id ? 'bg-white text-slate-900' : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white' }}">
                    {{ $cat->icon ? $cat->icon . ' ' : '' }}{{ $cat->name }}
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- SERVICES CONTENT --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-12">

        @php $hasAny = false; @endphp

        @foreach($categories as $cat)
            @php
                $services = $cat->activeServices;
                if ($activeFilter && $activeFilter != $cat->id) continue;
                if ($services->isEmpty()) continue;
                $hasAny = true;
                $c = \App\Models\ServiceCategory::colorClasses()[$cat->color] ?? \App\Models\ServiceCategory::colorClasses()['slate'];
            @endphp

            <div>
                {{-- CATEGORY HEADER --}}
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 rounded-2xl {{ $c['bg'] }} border {{ $c['border'] }} flex items-center justify-center text-2xl shadow-sm">
                        {{ $cat->icon ?: '📁' }}
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-900">{{ $cat->name }}</h2>
                        @if($cat->description)
                            <p class="text-sm text-slate-400">{{ $cat->description }}</p>
                        @endif
                    </div>
                    <div class="flex-1 h-px bg-slate-200 ml-4"></div>
                    <span class="text-xs font-bold text-slate-400">{{ $services->count() }} {{ Str::plural('service', $services->count()) }}</span>
                </div>

                {{-- SERVICES GRID --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($services as $service)
                    @php
                        $price = $service->prices->first();
                        $priceLabel = match($price?->pricing_type ?? 'fixed') {
                            'hourly' => 'R' . number_format($price->price, 2) . '/hr',
                            'range'  => 'R' . number_format($price->min_price, 2) . ' – R' . number_format($price->max_price, 2),
                            'custom' => 'Quote on request',
                            default  => $price ? 'R' . number_format($price->price, 2) : 'Price on request',
                        };
                    @endphp
                    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group flex flex-col">

                        {{-- COLOR STRIP --}}
                        <div class="h-1.5 {{ $c['accent'] }}"></div>

                        <div class="p-5 flex flex-col flex-1">
                            {{-- NAME & CATEGORY BADGE --}}
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <h3 class="font-black text-slate-900 text-base leading-snug group-hover:{{ $c['text'] }} transition-colors">
                                    {{ $service->name }}
                                </h3>
                                <span class="flex-shrink-0 text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full {{ $c['bg'] }} {{ $c['text'] }} border {{ $c['border'] }}">
                                    {{ $cat->icon ?: '' }} {{ $cat->name }}
                                </span>
                            </div>

                            {{-- DESCRIPTION --}}
                            @if($service->description)
                            <p class="text-sm text-slate-500 leading-relaxed flex-1 mb-4 line-clamp-3">
                                {{ $service->description }}
                            </p>
                            @else
                            <div class="flex-1"></div>
                            @endif

                            {{-- META ROW --}}
                            <div class="flex items-center gap-3 mt-auto pt-4 border-t border-slate-100">
                                @if($service->duration_label)
                                <span class="flex items-center gap-1 text-xs text-slate-500 font-semibold">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ $service->duration_label }}
                                </span>
                                @endif

                                <div class="flex-1"></div>

                                <span class="text-base font-black text-slate-900 tabular-nums">
                                    {{ $priceLabel }}
                                </span>
                            </div>

                            {{-- ITEMS / ADD-ONS --}}
                            @if($service->items->isNotEmpty())
                            <div class="mt-3 pt-3 border-t border-slate-100 space-y-1">
                                @foreach($service->items->take(4) as $item)
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-1.5">
                                        @if($item->is_included)
                                            <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                        @else
                                            <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        @endif
                                        <span class="{{ $item->is_included ? 'text-slate-600' : 'text-slate-400' }} font-medium">{{ $item->name }}</span>
                                    </div>
                                    @if(!$item->is_included && $item->price)
                                        <span class="text-slate-500 font-semibold tabular-nums">+R{{ number_format($item->price, 2) }}</span>
                                    @elseif($item->is_included)
                                        <span class="text-emerald-600 font-bold text-[10px] uppercase">Included</span>
                                    @endif
                                </div>
                                @endforeach
                                @if($service->items->count() > 4)
                                    <p class="text-[10px] text-slate-400 font-bold">+{{ $service->items->count() - 4 }} more add-ons</p>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- UNCATEGORIZED --}}
        @if($uncategorized->isNotEmpty() && !$activeFilter)
        @php $hasAny = true; @endphp
        <div>
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-2xl">
                    📋
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900">Other Services</h2>
                </div>
                <div class="flex-1 h-px bg-slate-200 ml-4"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($uncategorized as $service)
                @php
                    $price = $service->prices->first();
                    $priceLabel = match($price?->pricing_type ?? 'fixed') {
                        'hourly' => 'R' . number_format($price->price, 2) . '/hr',
                        'range'  => 'R' . number_format($price->min_price, 2) . ' – R' . number_format($price->max_price, 2),
                        'custom' => 'Quote on request',
                        default  => $price ? 'R' . number_format($price->price, 2) : 'Price on request',
                    };
                @endphp
                <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 group flex flex-col">
                    <div class="h-1.5 bg-slate-300"></div>
                    <div class="p-5 flex flex-col flex-1">
                        <h3 class="font-black text-slate-900 text-base leading-snug mb-3">{{ $service->name }}</h3>
                        @if($service->description)
                        <p class="text-sm text-slate-500 leading-relaxed flex-1 mb-4 line-clamp-3">{{ $service->description }}</p>
                        @else
                        <div class="flex-1"></div>
                        @endif
                        <div class="flex items-center gap-3 mt-auto pt-4 border-t border-slate-100">
                            @if($service->duration_label)
                            <span class="flex items-center gap-1 text-xs text-slate-500 font-semibold">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                {{ $service->duration_label }}
                            </span>
                            @endif
                            <div class="flex-1"></div>
                            <span class="text-base font-black text-slate-900 tabular-nums">{{ $priceLabel }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if(!$hasAny)
        <div class="text-center py-24">
            <div class="w-16 h-16 bg-white rounded-2xl border border-slate-200 flex items-center justify-center mx-auto mb-4 shadow-sm">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <p class="font-bold text-slate-400 text-sm uppercase tracking-widest">No services available</p>
            <a href="{{ route('products.create') }}" class="mt-4 inline-block text-amber-600 hover:text-amber-700 font-bold text-sm">
                Add your first service →
            </a>
        </div>
        @endif

    </div>
</div>
</x-app-layout>
