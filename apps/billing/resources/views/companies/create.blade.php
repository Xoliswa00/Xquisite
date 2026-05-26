<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">New Company</h2>
            <a href="{{ route('companies.index') }}" class="text-sm text-gray-500 hover:underline">Cancel</a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-6">

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
                    <ul class="list-disc pl-4 space-y-1 text-sm">
                        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('companies.store') }}" class="space-y-6">
                @csrf

                <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                    <h3 class="font-semibold text-slate-800">Company Details</h3>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Company Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm @error('name') border-red-500 @enderror">
                            @error('name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Legal Name</label>
                            <input type="text" name="legal_name" value="{{ old('legal_name') }}"
                                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Entity Type</label>
                            <select name="entity_type" class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                                <option value="sole_proprietor">Sole Proprietor</option>
                                <option value="private_company">Private Company (Pty) Ltd</option>
                                <option value="partnership">Partnership</option>
                                <option value="trust">Trust</option>
                                <option value="non_profit">Non Profit</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">VAT Number</label>
                            <input type="text" name="vat_number" value="{{ old('vat_number') }}"
                                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}"
                                   class="mt-1 w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('companies.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
                    <button type="submit"
                            class="px-6 py-2 bg-slate-900 text-white rounded-md text-sm hover:bg-slate-700">
                        Create Company
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
