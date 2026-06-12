<x-app-layout>
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-8">
        <div>
            <nav class="flex mb-2">
                <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    <li><a href="{{ route('products.index') }}" class="hover:text-slate-700">Services</a></li>
                    <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                    <li class="text-slate-900">Service Categories</li>
                </ol>
            </nav>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Service Categories</h2>
            <p class="text-slate-400 text-sm mt-1">Organise your services into categories for the client booking menu.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('booking.menu') }}"
               class="inline-flex items-center justify-center border border-slate-200 hover:border-slate-900 text-slate-600 hover:text-slate-900 px-4 py-2.5 rounded-xl font-bold text-sm transition-all">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                Preview Booking Page
            </a>
            <a href="{{ route('service-categories.create') }}"
               class="inline-flex items-center justify-center bg-slate-900 hover:bg-amber-400 hover:text-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-300 shadow-lg shadow-slate-200 group">
                <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Add Category
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold px-4 py-3 rounded-xl">
        {{ session('success') }}
    </div>
    @endif

    @forelse($categories as $cat)
    @php
        $colors = \App\Models\ServiceCategory::colorClasses();
        $c = $colors[$cat->color] ?? $colors['slate'];
    @endphp
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} {{ $c['border'] }} border flex items-center justify-center text-xl">
                    {{ $cat->icon ?: '📁' }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-slate-900 text-base">{{ $cat->name }}</h3>
                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full {{ $c['bg'] }} {{ $c['text'] }} border {{ $c['border'] }}">
                            {{ $cat->services_count }} {{ Str::plural('service', $cat->services_count) }}
                        </span>
                    </div>
                    @if($cat->description)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $cat->description }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[10px] font-bold text-slate-400">Order: {{ $cat->sort_order }}</span>
                @if($cat->is_active)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">
                        <span class="w-1 h-1 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Active
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-400 border border-slate-200">
                        Inactive
                    </span>
                @endif
                <a href="{{ route('service-categories.edit', $cat) }}" class="text-slate-400 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                </a>
                <form method="POST" action="{{ route('service-categories.destroy', $cat) }}" class="inline">
                    @csrf @method('DELETE')
                    <button onclick="return confirm('Delete this category? Services in it will become uncategorized.')"
                            class="text-slate-300 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </div>
        </div>

        @if($cat->services->isNotEmpty())
        <div class="px-6 py-3 flex flex-wrap gap-2">
            @foreach($cat->services as $service)
            <span class="text-[11px] font-semibold text-slate-600 bg-slate-50 border border-slate-200 px-3 py-1 rounded-lg">
                {{ $service->name }}
            </span>
            @endforeach
        </div>
        @else
        <p class="px-6 py-4 text-xs text-slate-400 italic">No services assigned yet.</p>
        @endif
    </div>
    @empty
    <div class="text-center py-20">
        <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
        </div>
        <p class="font-bold text-slate-400 text-sm uppercase tracking-widest">No categories yet</p>
        <p class="text-slate-400 text-xs mt-1">Create categories like "Nails", "Hair", "Massage" to organise your services.</p>
        <a href="{{ route('service-categories.create') }}" class="mt-4 inline-block text-amber-600 hover:text-amber-700 font-bold text-sm">
            Create your first category →
        </a>
    </div>
    @endforelse

</div>
</x-app-layout>
