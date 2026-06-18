<x-app-layout>
    <x-slot name="header">Edit Supplier — {{ $supplier->name }}</x-slot>

    <div class="max-w-xl space-y-4">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('suppliers.update', $supplier) }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Supplier Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Payment Terms</label>
                        <input type="text" name="payment_terms" value="{{ old('payment_terms', $supplier->payment_terms) }}"
                               placeholder="e.g. 30 days, COD"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Website</label>
                    <input type="url" name="website" value="{{ old('website', $supplier->website) }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Address</label>
                    <textarea name="address" rows="2"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old('address', $supplier->address) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old('notes', $supplier->notes) }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                           class="rounded bg-slate-700 border-slate-600 text-[#0078D4] focus:ring-[#0078D4]">
                    <label for="is_active" class="text-sm text-slate-300">Active supplier</label>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-6 py-2 rounded-lg">Save Changes</button>
                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
