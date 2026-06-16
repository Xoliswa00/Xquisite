<x-app-layout>
    <x-slot name="header">Service Categories</x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-white">Service Categories</h2>
                <p class="text-sm text-slate-400 mt-1">Organise your services for the booking menu.</p>
            </div>
            <a href="{{ route('service-categories.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition-colors">
                + New Category
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($categories as $cat)
                @php $classes = \App\Models\ServiceCategory::colorClasses()[$cat->color] ?? []; @endphp
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl leading-none">{{ $cat->icon }}</span>
                            <div>
                                <p class="font-semibold text-white">{{ $cat->name }}</p>
                                <p class="text-xs text-slate-400">{{ $cat->services_count }} service{{ $cat->services_count !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        <span class="inline-block w-3 h-3 rounded-full {{ $classes['dot'] ?? 'bg-slate-500' }}"></span>
                    </div>

                    @if($cat->description)
                        <p class="text-sm text-slate-400 line-clamp-2">{{ $cat->description }}</p>
                    @endif

                    <div class="flex items-center gap-2 mt-auto pt-2 border-t border-slate-800">
                        @if(!$cat->is_active)
                            <span class="text-xs text-red-400 px-2 py-0.5 rounded bg-red-900/30">Inactive</span>
                        @endif
                        <a href="{{ route('service-categories.edit', $cat) }}" class="text-xs px-3 py-1.5 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-700">Edit</a>
                        <form method="POST" action="{{ route('service-categories.destroy', $cat) }}" onsubmit="return confirm('Delete this category?')">
                            @csrf @method('DELETE')
                            <button class="text-xs px-3 py-1.5 rounded-lg border border-red-800 text-red-400 hover:bg-red-900/30">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-3 py-10 text-center text-slate-400 text-sm">
                    No categories yet. <a href="{{ route('service-categories.create') }}" class="text-indigo-400 hover:underline">Create one.</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
