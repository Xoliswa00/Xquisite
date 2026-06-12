<x-app-layout>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-slate-800">
                Edit Company
            </h2>

            <a href="{{ route('companies.show', $company) }}"
                class="text-sm text-gray-600 hover:underline">
                Cancel
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto px-6">

            <form method="POST"
                action="{{ route('companies.update', $company) }}"
                enctype="multipart/form-data"
                class="space-y-8">

                @csrf
                @method('PUT')

                {{-- Global Errors --}}
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded">
                        <p class="font-semibold mb-2">Please fix the following errors:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- COMPANY IDENTITY --}}
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6">Company Identity</h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm text-gray-600">Company Name</label>
                            <input type="text"
                                name="name"
                                value="{{ old('name',$company->name) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Legal Name</label>
                            <input type="text"
                                name="legal_name"
                                value="{{ old('legal_name',$company->legal_name) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('legal_name') border-red-500 @enderror">
                            @error('legal_name')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Entity Type</label>
                            <select name="entity_type"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('entity_type') border-red-500 @enderror">
                                <option value="sole_proprietor" {{ old('entity_type',$company->entity_type)=='sole_proprietor' ? 'selected' : '' }}>Sole Proprietor</option>
                                <option value="private_company" {{ old('entity_type',$company->entity_type)=='private_company' ? 'selected' : '' }}>Private Company</option>
                                <option value="partnership" {{ old('entity_type',$company->entity_type)=='partnership' ? 'selected' : '' }}>Partnership</option>
                                <option value="trust" {{ old('entity_type',$company->entity_type)=='trust' ? 'selected' : '' }}>Trust</option>
                                <option value="non_profit" {{ old('entity_type',$company->entity_type)=='non_profit' ? 'selected' : '' }}>Non Profit</option>
                                <option value="other" {{ old('entity_type',$company->entity_type)=='other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('entity_type')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Workspace Slug</label>
                            <input type="text"
                                name="slug"
                                value="{{ old('slug',$company->slug) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('slug') border-red-500 @enderror">
                            @error('slug')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- BUSINESS REGISTRATION --}}
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6">Business Registration</h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm text-gray-600">Registration Number</label>
                            <input type="text"
                                name="registration_number"
                                value="{{ old('registration_number',$company->registration_number) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('registration_number') border-red-500 @enderror">
                            @error('registration_number')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Tax Number</label>
                            <input type="text"
                                name="tax_number"
                                value="{{ old('tax_number',$company->tax_number) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('tax_number') border-red-500 @enderror">
                            @error('tax_number')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">VAT Number</label>
                            <input type="text"
                                name="vat_number"
                                value="{{ old('vat_number',$company->vat_number) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('vat_number') border-red-500 @enderror">
                            @error('vat_number')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- CONTACT INFORMATION --}}
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6">Contact Information</h3>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <label>Email</label>
                            <input type="email"
                                name="email"
                                value="{{ old('email',$company->email) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label>Phone</label>
                            <input type="text"
                                name="phone"
                                value="{{ old('phone',$company->phone) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label>Website</label>
                            <input type="text"
                                name="website"
                                value="{{ old('website',$company->website) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('website') border-red-500 @enderror">
                            @error('website')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label>Billing Email</label>
                            <input type="email"
                                name="billing_email"
                                value="{{ old('billing_email',$company->billing_email) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('billing_email') border-red-500 @enderror">
                            @error('billing_email')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- BUSINESS ADDRESS --}}
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6">Business Address</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        @php $addressFields = ['address_line_1','address_line_2','city','province','postal_code','country']; @endphp
                        @foreach($addressFields as $field)
                            <div>
                                <input type="text"
                                    name="{{ $field }}"
                                    placeholder="{{ ucwords(str_replace('_',' ',$field)) }}"
                                    value="{{ old($field, $company->$field) }}"
                                    class="w-full mt-1 border rounded-lg px-3 py-2 @error($field) border-red-500 @enderror">
                                @error($field)
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- FINANCIAL SETTINGS --}}
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6">Financial Settings</h3>
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <input type="text"
                                name="currency"
                                value="{{ old('currency',$company->currency) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('currency') border-red-500 @enderror">
                            @error('currency')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <input type="number"
                                step="0.01"
                                name="default_vat_rate"
                                value="{{ old('default_vat_rate',$company->default_vat_rate) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('default_vat_rate') border-red-500 @enderror">
                            @error('default_vat_rate')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <label class="flex items-center gap-3 mt-3">
                            <input type="checkbox"
                                name="vat_registered"
                                value="1"
                                {{ old('vat_registered', $company->vat_registered) ? 'checked' : '' }}>
                            VAT Registered
                        </label>
                        @error('vat_registered')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- WORKSPACE --}}
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6">Workspace Settings</h3>
                    <div class="grid md:grid-cols-2 gap-6">

                        <div>
                            <input type="text"
                                name="domain"
                                placeholder="Company Domain"
                                value="{{ old('domain',$company->domain) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('domain') border-red-500 @enderror">
                            @error('domain')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <input type="text"
                                name="timezone"
                                value="{{ old('timezone',$company->timezone) }}"
                                class="w-full mt-1 border rounded-lg px-3 py-2 @error('timezone') border-red-500 @enderror">
                            @error('timezone')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm text-gray-600">Company Logo</label>
                            <input type="file"
                                name="logo"
                                class="mt-2 @error('logo') border-red-500 @enderror">
                            @error('logo')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- SUBSCRIPTION --}}
                <div class="bg-white p-8 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-lg font-semibold mb-6">Subscription</h3>
                    <div class="grid md:grid-cols-2 gap-6">

                        <select name="plan"
                            class="w-full mt-1 border rounded-lg px-3 py-2 @error('plan') border-red-500 @enderror">
                            <option value="basic" {{ old('plan',$company->plan)=='basic' ? 'selected' : '' }}>Basic</option>
                            <option value="pro" {{ old('plan',$company->plan)=='pro' ? 'selected' : '' }}>Pro</option>
                            <option value="enterprise" {{ old('plan',$company->plan)=='enterprise' ? 'selected' : '' }}>Enterprise</option>
                        </select>
                        @error('plan')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror

                        <select name="status"
                            class="w-full mt-1 border rounded-lg px-3 py-2 @error('status') border-red-500 @enderror">
                            <option value="active" {{ old('status',$company->status)=='active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ old('status',$company->status)=='suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="closed" {{ old('status',$company->status)=='closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                        @error('status')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror

                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-slate-900 text-white px-6 py-3 rounded-lg hover:bg-slate-700">
                        Save Changes
                    </button>
                </div>

            </form>

        </div>
    </div>

</x-app-layout>