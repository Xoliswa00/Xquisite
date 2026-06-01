<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.platform-services.index') }}" class="text-gray-400 hover:text-gray-600">← Services</a>
            <span class="text-gray-300">/</span>
            <h2 class="text-xl font-semibold">New Service</h2>
        </div>
    </x-slot>
    <div class="py-8 max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.platform-services.store') }}"
              class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
            @csrf
            @include('admin.platform-services._form')
            <div class="px-6 py-4 flex justify-end gap-3">
                <a href="{{ route('admin.platform-services.index') }}" class="px-4 py-2 text-sm text-gray-600">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm rounded-lg font-medium">Add Service</button>
            </div>
        </form>
    </div>
</x-app-layout>
