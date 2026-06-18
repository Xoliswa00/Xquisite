<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.plans.index') }}" class="text-slate-400 hover:text-white transition-colors">← Plans</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-xl font-bold text-[#D4AF37]">New Plan</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('admin.plans.store') }}"
              class="bg-slate-800 rounded-xl border border-slate-700 divide-y divide-slate-700">
            @csrf
            @include('admin.plans._form')
            <div class="px-6 py-4 flex flex-col sm:flex-row-reverse gap-3">
                <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm rounded-lg font-medium transition-colors">
                    Create Plan
                </button>
                <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 text-sm text-slate-400 hover:text-white text-center transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
