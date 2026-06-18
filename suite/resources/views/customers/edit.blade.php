<x-app-layout>
    <x-slot name="header">Edit Customer</x-slot>

    <div class="max-w-xl space-y-4">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Full Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old('notes', $customer->notes) }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                    <label for="is_active" class="text-sm text-slate-300">Active</label>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-6 py-2 rounded-lg">Save Changes</button>
                    <a href="{{ route('customers.show', $customer) }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>

        <div class="bg-slate-800 rounded-xl p-4 border border-red-900/50">
            <p class="text-sm text-slate-400 mb-3">Permanently remove this customer and all their appointments.</p>
            <form method="POST" action="{{ route('customers.destroy', $customer) }}"
                  onsubmit="return confirm('Delete {{ addslashes($customer->name) }}?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-700 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg">
                    Delete Customer
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
