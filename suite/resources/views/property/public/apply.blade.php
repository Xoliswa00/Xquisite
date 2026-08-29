<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply — {{ $property->name }} — {{ $tenant->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
        @if(!empty($tenant->logo_url))
            <img src="{{ $tenant->logo_url }}" alt="{{ $tenant->name }}" class="w-9 h-9 rounded-lg object-cover shrink-0">
        @else
            <span class="w-9 h-9 rounded-lg bg-[#0078D4] flex items-center justify-center text-white font-black text-sm shrink-0">
                {{ strtoupper(substr($tenant->name, 0, 1)) }}
            </span>
        @endif
        <div class="min-w-0">
            <span class="block text-lg font-bold text-slate-900 truncate">{{ $tenant->name }}</span>
            <span class="text-xs text-slate-400 font-medium uppercase tracking-wide">Rental Application</span>
        </div>
    </div>
</header>

<main class="max-w-2xl mx-auto px-4 py-10">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Apply to Rent</h1>
        <p class="text-slate-500 text-sm mt-1">
            {{ $property->name }}{{ $preUnit ? ' — Unit ' . $preUnit->unit_number : '' }}, {{ $property->address_line_1 }}, {{ $property->city }}
        </p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('apply.store', [$slug, $property]) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @if($preUnit)
            <input type="hidden" name="unit_id" value="{{ $preUnit->id }}">
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-slate-800">Personal Details</h2>

            @if(!$preUnit && $vacantUnits->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Unit you're interested in</label>
                    <select name="unit_id" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Not sure yet</option>
                        @foreach($vacantUnits as $u)
                            <option value="{{ $u->id }}" @selected(old('unit_id') == $u->id)>Unit {{ $u->unit_number }} — R{{ number_format($u->monthly_rent, 2) }}/month</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-slate-300 rounded-xl text-sm">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Phone *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required class="w-full border-slate-300 rounded-xl text-sm">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ID Number</label>
                <input type="text" name="id_number" value="{{ old('id_number') }}" class="w-full border-slate-300 rounded-xl text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Number of Occupants</label>
                <input type="number" name="number_of_occupants" value="{{ old('number_of_occupants') }}" min="0" class="w-full border-slate-300 rounded-xl text-sm">
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-slate-800">Employment &amp; Income</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Employer</label>
                    <input type="text" name="employer" value="{{ old('employer') }}" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Employment Type</label>
                    <select name="employment_type" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Not specified</option>
                        @foreach(['permanent','contract','self_employed','unemployed','other'] as $t)
                            <option value="{{ $t }}" @selected(old('employment_type') === $t)>{{ ucfirst(str_replace('_', ' ', $t)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Months Employed</label>
                    <input type="number" name="employment_months" value="{{ old('employment_months') }}" min="0" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Income (R)</label>
                    <input type="number" name="monthly_income" value="{{ old('monthly_income') }}" step="0.01" min="0" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Monthly Expenses / Debt (R)</label>
                    <input type="number" name="monthly_expenses" value="{{ old('monthly_expenses') }}" step="0.01" min="0" class="w-full border-slate-300 rounded-xl text-sm">
                    <p class="text-xs text-slate-400 mt-1">Existing debt or recurring obligations, excluding rent.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-slate-800">Rental History</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Previous Landlord Name</label>
                    <input type="text" name="previous_landlord_name" value="{{ old('previous_landlord_name') }}" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Previous Landlord Phone</label>
                    <input type="text" name="previous_landlord_phone" value="{{ old('previous_landlord_phone') }}" class="w-full border-slate-300 rounded-xl text-sm">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            <h2 class="text-base font-semibold text-slate-800">Supporting Documents</h2>
            <p class="text-sm text-slate-500">Upload clear photos or PDFs. JPG, PNG, HEIC or PDF, max 15MB each.</p>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">ID Copy</label>
                <input type="file" name="documents[id_copy][]" multiple accept="image/png,image/jpeg,image/heic,image/heif,image/webp,application/pdf"
                       class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:text-sm hover:file:bg-slate-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Proof of Income (payslip)</label>
                <input type="file" name="documents[proof_of_income][]" multiple accept="image/png,image/jpeg,image/heic,image/heif,image/webp,application/pdf"
                       class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:text-sm hover:file:bg-slate-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Bank Statement</label>
                <input type="file" name="documents[bank_statement][]" multiple accept="image/png,image/jpeg,image/heic,image/heif,image/webp,application/pdf"
                       class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:text-sm hover:file:bg-slate-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Proof of Residence</label>
                <input type="file" name="documents[proof_of_residence][]" multiple accept="image/png,image/jpeg,image/heic,image/heif,image/webp,application/pdf"
                       class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 file:text-sm hover:file:bg-slate-200">
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-6">
            <label class="block text-sm font-medium text-slate-700 mb-1">Anything else you'd like us to know?</label>
            <textarea name="notes" rows="3" class="w-full border-slate-300 rounded-xl text-sm">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="w-full py-3 bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl">
            Submit Application
        </button>
    </form>

</main>

<footer class="border-t border-slate-200 mt-10 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>
</body>
</html>
