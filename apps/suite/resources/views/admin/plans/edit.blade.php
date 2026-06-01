<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.plans.index') }}" class="text-gray-400 hover:text-gray-600">← Plans</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold">{{ $plan->name }}</h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.plans.update', $plan) }}"
              class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @csrf @method('PUT')
            @include('admin.plans._form', ['plan' => $plan, 'selectedModules' => $selectedModules])
            <div class="px-6 py-4 flex justify-end gap-3">
                <a href="{{ route('admin.plans.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
