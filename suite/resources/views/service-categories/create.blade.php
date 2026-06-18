<x-app-layout>
    <x-slot name="header">{{ isset($category) ? 'Edit Category' : 'New Category' }}</x-slot>

    <div class="max-w-xl mx-auto" x-data="{ icon: '{{ old('icon', $category->icon ?? '✨') }}', color: '{{ old('color', $category->color ?? 'indigo') }}' }">
        <form method="POST" action="{{ isset($category) ? route('service-categories.update', $category) : route('service-categories.store') }}" class="space-y-5">
            @csrf
            @if(isset($category)) @method('PUT') @endif
            <x-form-errors />

            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                {{-- Icon preview --}}
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-xl bg-slate-800 flex items-center justify-center text-3xl" x-text="icon"></div>
                    <div class="flex-1">
                        <x-input-label value="Icon (emoji)" />
                        <x-text-input name="icon" x-model="icon" class="mt-1 w-full" value="{{ old('icon', $category->icon ?? '✨') }}" maxlength="10" placeholder="✨" />
                    </div>
                </div>

                <div>
                    <x-input-label value="Name" />
                    <x-text-input name="name" class="mt-1 w-full" value="{{ old('name', $category->name ?? '') }}" required />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label value="Description" />
                    <textarea name="description" rows="2" class="mt-1 w-full rounded-lg bg-slate-800 border-slate-700 text-slate-200 text-sm focus:ring-[#0078D4] focus:border-[#0078D4]">{{ old('description', $category->description ?? '') }}</textarea>
                </div>

                {{-- Color grid --}}
                <div>
                    <x-input-label value="Color" />
                    <div class="mt-2 grid grid-cols-4 gap-2">
                        @foreach(\App\Models\ServiceCategory::colorClasses() as $colorKey => $classes)
                            <label class="relative cursor-pointer">
                                <input type="radio" name="color" value="{{ $colorKey }}" x-model="color" class="sr-only" {{ old('color', $category->color ?? 'indigo') === $colorKey ? 'checked' : '' }}>
                                <div class="h-10 rounded-lg {{ $classes['bg'] }} {{ $classes['border'] }} border-2 flex items-center justify-center transition-all"
                                     :class="color === '{{ $colorKey }}' ? 'ring-2 ring-white ring-offset-2 ring-offset-slate-900' : ''">
                                    <span class="text-xs font-medium {{ $classes['text'] }}">{{ ucfirst($colorKey) }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <x-input-label value="Sort Order" />
                    <x-text-input name="sort_order" type="number" min="0" class="mt-1 w-full" value="{{ old('sort_order', $category->sort_order ?? 0) }}" />
                </div>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-600 bg-slate-800 text-[#0078D4]">
                    <span class="text-sm text-slate-300">Active</span>
                </label>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="submit" class="px-6 py-2.5 bg-[#0078D4] hover:bg-[#0078D4] text-white font-medium rounded-lg text-sm transition-colors">
                    {{ isset($category) ? 'Update' : 'Create Category' }}
                </button>
                <a href="{{ route('service-categories.index') }}" class="px-6 py-2.5 border border-slate-700 text-slate-300 hover:bg-slate-800 rounded-lg text-sm text-center">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
