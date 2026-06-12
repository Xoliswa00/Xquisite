<x-app-layout>
<div class="max-w-xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

    <div class="border-b border-slate-200 pb-8">
        <nav class="flex mb-2">
            <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                <li><a href="{{ route('service-categories.index') }}" class="hover:text-slate-700">Categories</a></li>
                <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                <li class="text-slate-900">{{ isset($serviceCategory) ? 'Edit' : 'New Category' }}</li>
            </ol>
        </nav>
        <h2 class="text-3xl font-black text-slate-900 tracking-tighter">
            {{ isset($serviceCategory) ? 'Edit: ' . $serviceCategory->name : 'New Service Category' }}
        </h2>
    </div>

    @if($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-sm text-rose-600">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ isset($serviceCategory) ? route('service-categories.update', $serviceCategory) : route('service-categories.store') }}"
          x-data="{ icon: '{{ old('icon', $serviceCategory->icon ?? '') }}', color: '{{ old('color', $serviceCategory->color ?? 'slate') }}' }"
          class="space-y-6">
        @csrf
        @if(isset($serviceCategory)) @method('PUT') @endif

        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm space-y-5">

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Category Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $serviceCategory->name ?? '') }}"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                       placeholder="e.g. Nails, Hair & Color, Massage" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Description</label>
                <textarea name="description" rows="2"
                          class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 resize-none"
                          placeholder="Short description for clients...">{{ old('description', $serviceCategory->description ?? '') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Emoji Icon</label>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-center text-2xl" x-text="icon || '📁'"></div>
                    <input type="text" name="icon" x-model="icon"
                           maxlength="4"
                           class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                           placeholder="Paste an emoji, e.g. 💅 ✂️ 💆">
                </div>
                <p class="text-xs text-slate-400 mt-1">Common: 💅 ✂️ 💆 💇 💄 🧴 🧖 🌿 💎 🪷</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-3 uppercase tracking-wider">Accent Color</label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach($colors as $key => $cls)
                    <label class="cursor-pointer">
                        <input type="radio" name="color" value="{{ $key }}" x-model="color"
                               {{ old('color', $serviceCategory->color ?? 'slate') === $key ? 'checked' : '' }}
                               class="sr-only">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl border-2 transition-all text-xs font-bold"
                             :class="color === '{{ $key }}' ? 'border-slate-900 {{ $cls['bg'] }} {{ $cls['text'] }}' : 'border-slate-100 {{ $cls['bg'] }} {{ $cls['text'] }} opacity-60'">
                            <span class="w-3 h-3 rounded-full {{ $cls['accent'] }}"></span>
                            {{ ucfirst($key) }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Sort Order</label>
                    <input type="number" name="sort_order" min="0"
                           value="{{ old('sort_order', $serviceCategory->sort_order ?? 0) }}"
                           class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-slate-900">
                    <p class="text-xs text-slate-400 mt-1">Lower = appears first</p>
                </div>
                <div class="flex items-end pb-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                               {{ old('is_active', $serviceCategory->is_active ?? true) ? 'checked' : '' }}
                               class="w-5 h-5 rounded accent-slate-900">
                        <div>
                            <p class="text-sm font-bold text-slate-800">Active</p>
                            <p class="text-xs text-slate-400">Show on booking page</p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <div class="space-y-3">
            <button type="submit"
                    class="w-full bg-slate-900 hover:bg-amber-400 hover:text-slate-900 text-white font-bold py-3 rounded-xl text-sm transition-all duration-300 shadow-lg shadow-slate-200">
                {{ isset($serviceCategory) ? 'Update Category' : 'Create Category' }}
            </button>
            <a href="{{ route('service-categories.index') }}"
               class="block w-full text-center bg-white border border-slate-200 text-slate-600 hover:text-slate-900 font-semibold py-3 rounded-xl text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
</x-app-layout>
