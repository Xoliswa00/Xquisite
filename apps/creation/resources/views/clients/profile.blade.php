<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4">

        <h1 class="text-xl font-bold text-slate-900 mb-6">
            Company Profile
        </h1>

        @if(session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('clients.update', $client->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="text-sm font-medium text-slate-700">Company Name</label>
                <input type="text" name="name" value="{{ old('name', $client->name) }}"
                       class="w-full mt-1 rounded-lg border-slate-300">
            </div>

            <div>
                <label class="text-sm font-medium text-slate-700">Contact Person</label>
                <input type="text" name="contact_person" value="{{ old('contact_person', $client->contact_person) }}"
                       class="w-full mt-1 rounded-lg border-slate-300">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm">Email</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                           class="w-full mt-1 rounded-lg border-slate-300">
                </div>

                <div>
                    <label class="text-sm">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                           class="w-full mt-1 rounded-lg border-slate-300">
                </div>
            </div>

            <div>
                <label class="text-sm">Billing Address</label>
                <textarea name="billing_address"
                          class="w-full mt-1 rounded-lg border-slate-300">{{ old('billing_address', $client->billing_address) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm">VAT Number</label>
                    <input type="text" name="vat_number" value="{{ old('vat_number', $client->vat_number) }}"
                           class="w-full mt-1 rounded-lg border-slate-300">
                </div>

                <div>
                    <label class="text-sm">Registration Number</label>
                    <input type="text" name="registration_number" value="{{ old('registration_number', $client->registration_number) }}"
                           class="w-full mt-1 rounded-lg border-slate-300">
                </div>
            </div>

            <div class="pt-4">
                <button class="px-4 py-2 bg-slate-900 text-white rounded-lg">
                    Save Changes
                </button>
            </div>

        </form>
    </div>
</x-app-layout>