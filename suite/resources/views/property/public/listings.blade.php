<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rentals — {{ $tenant->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center gap-3">
        @if(!empty($tenant->logo_url))
            <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="w-9 h-9 rounded-lg object-cover shrink-0">
        @else
            <span class="w-9 h-9 rounded-lg bg-[#0078D4] flex items-center justify-center text-white font-black text-sm shrink-0">
                {{ strtoupper(substr($tenant->name, 0, 1)) }}
            </span>
        @endif
        <div class="min-w-0">
            <span class="block text-lg font-bold text-slate-900 truncate">{{ $tenant->name }}</span>
            <span class="text-xs text-slate-400 font-medium uppercase tracking-wide">Available Rentals</span>
        </div>
    </div>
</header>

<main>
    {{-- Hero + search --}}
    <section class="bg-[#002B5B] px-4 py-14 sm:py-20">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-white leading-tight">
                Find your next place with
                <span class="text-[#D4AF37]">{{ $tenant->name }}</span>
            </h1>
            <p class="mt-3 text-[#B8D4F0] text-sm sm:text-base max-w-xl mx-auto">
                {{ $properties->count() }} {{ Str::plural('property', $properties->count()) }} available to rent right now.
            </p>
        </div>

        <form method="GET" action="{{ route('listings.index', $slug) }}"
              class="max-w-3xl mx-auto mt-8 bg-white rounded-2xl p-3 sm:p-4 flex flex-col sm:flex-row gap-3 shadow-lg">
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-500 mb-1 px-1">Location</label>
                <select name="city" class="w-full border-slate-200 rounded-xl text-sm">
                    <option value="">Any location</option>
                    @foreach($cities as $c)
                        <option value="{{ $c }}" @selected(request('city') === $c)>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-500 mb-1 px-1">Property Type</label>
                <select name="type" class="w-full border-slate-200 rounded-xl text-sm">
                    <option value="">Any type</option>
                    @foreach($unitTypes as $t)
                        <option value="{{ $t }}" @selected(request('type') === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1">
                <label class="block text-xs font-medium text-slate-500 mb-1 px-1">Max Price</label>
                <select name="max_price" class="w-full border-slate-200 rounded-xl text-sm">
                    <option value="">Any price</option>
                    @foreach([5000, 10000, 15000, 20000, 30000, 50000] as $p)
                        <option value="{{ $p }}" @selected((int) request('max_price') === $p)>Up to R{{ number_format($p) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-semibold rounded-xl transition">
                    Search
                </button>
            </div>
        </form>
    </section>

    {{-- Why choose us --}}
    <section class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
        <div>
            <div class="w-10 h-10 mx-auto rounded-full bg-[#0078D4]/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#0078D4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="mt-2 text-sm font-semibold text-slate-800">Verified Listings</p>
            <p class="text-xs text-slate-500 mt-0.5">Every listing here is managed directly by {{ $tenant->name }}.</p>
        </div>
        <div>
            <div class="w-10 h-10 mx-auto rounded-full bg-[#0078D4]/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#0078D4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <p class="mt-2 text-sm font-semibold text-slate-800">Secure Application</p>
            <p class="text-xs text-slate-500 mt-0.5">Apply online and upload your documents safely.</p>
        </div>
        <div>
            <div class="w-10 h-10 mx-auto rounded-full bg-[#0078D4]/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#0078D4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <p class="mt-2 text-sm font-semibold text-slate-800">Real-Time Availability</p>
            <p class="text-xs text-slate-500 mt-0.5">What you see here is what's actually open right now.</p>
        </div>
        <div>
            <div class="w-10 h-10 mx-auto rounded-full bg-[#0078D4]/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-[#0078D4]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <p class="mt-2 text-sm font-semibold text-slate-800">Direct Contact</p>
            <p class="text-xs text-slate-500 mt-0.5">Questions go straight to {{ $tenant->name }}, no middleman.</p>
        </div>
    </section>

    {{-- Listings grid --}}
    <section class="max-w-6xl mx-auto px-4 pb-14">
        <h2 class="text-xl font-bold text-slate-900 mb-5">
            @if(request()->hasAny(['city', 'type', 'max_price']))
                Matching Properties
            @else
                Available Properties
            @endif
        </h2>

        @if($properties->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 p-10 text-center">
                <p class="text-slate-500 text-sm">No properties match your search right now.</p>
                @if(request()->hasAny(['city', 'type', 'max_price']))
                    <a href="{{ route('listings.index', $slug) }}" class="text-[#0078D4] hover:text-[#0065B8] text-sm font-medium mt-2 inline-block">Clear filters &rarr;</a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($properties as $property)
                    @php $cheapest = $property->units->first(); @endphp
                    <a href="{{ route('apply.show', [$slug, $property]) }}"
                       class="block bg-white rounded-2xl border border-slate-200 overflow-hidden hover:border-[#0078D4]/40 hover:shadow-md transition">
                        <div class="aspect-[4/3] bg-slate-100">
                            @if($property->coverImage)
                                <img src="{{ $property->coverImage->url() }}" alt="{{ $property->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-4">
                            <p class="font-semibold text-slate-900 truncate">{{ $property->name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $property->city }}@if($property->province), {{ $property->province }}@endif</p>
                            @if($cheapest)
                                <div class="flex items-center justify-between mt-3">
                                    <p class="text-[#0078D4] font-bold">
                                        From R{{ number_format($cheapest->monthly_rent, 0) }}<span class="text-xs text-slate-400 font-normal">/mo</span>
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ $cheapest->bedrooms }} Bed{{ $cheapest->bedrooms === 1 ? '' : 's' }} &middot; {{ $cheapest->bathrooms }} Bath{{ $cheapest->bathrooms === 1 ? '' : 's' }}
                                    </p>
                                </div>
                                @if($property->units->count() > 1)
                                    <p class="text-xs text-slate-400 mt-1">{{ $property->units->count() }} units available</p>
                                @endif
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- Bottom CTA --}}
    <section class="bg-[#002B5B] px-4 py-10">
        <div class="max-w-4xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-center sm:text-left">
                <p class="text-white font-semibold text-lg">Can't find what you're looking for?</p>
                <p class="text-[#B8D4F0] text-sm mt-1">Get in touch with {{ $tenant->name }} directly.</p>
            </div>
            @if(!empty($tenant->phone))
                <x-whatsapp-link :phone="$tenant->phone" :message="'Hi, I\'m looking for a place to rent.'"
                    class="shrink-0 px-6 py-3 bg-[#D4AF37] hover:bg-[#c19c2e] !text-[#002B5B] hover:!text-[#002B5B] text-sm font-semibold rounded-xl transition">
                    Message us on WhatsApp
                </x-whatsapp-link>
            @endif
        </div>
    </section>
</main>

<footer class="border-t border-slate-200 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>
</body>
</html>
