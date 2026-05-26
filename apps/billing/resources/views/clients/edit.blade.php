<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800">Edit Client</h2>
            <a href="{{ route('clients.show', $client) }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
        </div>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto px-4">

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-300 text-red-700 p-4 rounded-lg text-sm">
                <ul class="list-disc pl-4 space-y-1">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow p-6">
            <form method="POST" action="{{ route('clients.update', $client) }}">
                @csrf
                @method('PUT')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name *</label>
                        <input type="text" name="name" value="{{ old('name', $client->name) }}" required
                               class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm @error('name') border-red-500 @enderror">
                        @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $client->contact_person) }}"
                               class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email', $client->email) }}"
                                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">VAT Number</label>
                        <input type="text" name="vat_number" value="{{ old('vat_number', $client->vat_number) }}"
                               class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Billing Address</label>
                        <textarea name="billing_address" rows="3"
                                  class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('billing_address', $client->billing_address) }}</textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('clients.show', $client) }}"
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit"
                            class="px-6 py-2 bg-slate-900 text-white rounded-md text-sm hover:bg-slate-700">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
