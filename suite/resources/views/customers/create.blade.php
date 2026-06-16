<x-app-layout>
    <x-slot name="header">New Customer</x-slot>

    <div class="max-w-xl">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                              placeholder="Allergies, preferences…">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm text-slate-300">Active</label>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded-lg">
                        Add Customer
                    </button>
                    <a href="{{ route('customers.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
