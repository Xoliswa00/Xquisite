<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-white">Add Property</h2>
            <a href="{{ route('properties.index') }}" class="text-sm text-slate-400 hover:text-white">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6">
        <form method="POST" action="{{ route('properties.store') }}" class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Property Details</h3>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Property Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Type *</label>
                        <select name="type" required class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                            @foreach(['residential','commercial','mixed','industrial'] as $t)
                                <option value="{{ $t }}" @selected(old('type') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Address Line 1 *</label>
                    <input type="text" name="address_line_1" value="{{ old('address_line_1') }}" required
                           class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Address Line 2</label>
                    <input type="text" name="address_line_2" value="{{ old('address_line_2') }}"
                           class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">City *</label>
                        <input type="text" name="city" value="{{ old('city') }}" required
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Province</label>
                        <input type="text" name="province" value="{{ old('province') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="bg-slate-800 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-slate-300">Owner / Landlord</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Name</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Email</label>
                        <input type="email" name="owner_email" value="{{ old('owner_email') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1">Phone</label>
                        <input type="text" name="owner_phone" value="{{ old('owner_phone') }}"
                               class="w-full bg-slate-700 border-slate-600 text-slate-100 rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('properties.index') }}"
                   class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-semibold">
                    Save Property
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
