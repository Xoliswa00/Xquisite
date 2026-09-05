<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $checkin->label() }} — Xquisite Creations Founding 20</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="w-9 h-9 rounded-lg object-contain shrink-0">
        <div class="min-w-0">
            <span class="block text-lg font-bold text-slate-900 truncate">Xquisite Creations</span>
            <span class="text-xs font-semibold uppercase tracking-wide text-[#D4AF37]">{{ $checkin->label() }}</span>
        </div>
    </div>
</header>

<main class="max-w-md mx-auto px-4 py-12">

    @if($checkin->isComplete())
        <div class="text-center">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center mx-auto mb-5">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="text-xl font-bold text-slate-900">Already submitted</h1>
            <p class="text-slate-500 text-sm mt-2">Thanks — we've already got your {{ $checkin->label() }}. Nothing more needed here.</p>
        </div>
    @else
        <div class="mb-6">
            <h1 class="text-xl font-bold text-slate-900">Quick {{ $checkin->label() }}</h1>
            <p class="text-slate-500 text-sm mt-2">A few of the same questions from your original application, so we can see what's actually changed. Takes about 2 minutes.</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('founding-twenty.checkin.store', [$checkin, $token]) }}" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Monthly appointments (approx.)</label>
                <select name="monthly_appointments" class="w-full border-slate-300 rounded-xl text-sm">
                    <option value="">Select…</option>
                    @foreach(['0-50' => '0–50', '51-150' => '51–150', '151-300' => '151–300', '300+' => '300+'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('monthly_appointments') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">No-shows per month (approx.)</label>
                <select name="no_shows_per_month" class="w-full border-slate-300 rounded-xl text-sm">
                    <option value="">Select…</option>
                    @foreach(['0' => '0', '1-2' => '1–2', '3-5' => '3–5', '6-10' => '6–10', '10+' => '10+'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('no_shows_per_month') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Average appointment value</label>
                <select name="avg_appointment_value" class="w-full border-slate-300 rounded-xl text-sm">
                    <option value="">Select…</option>
                    @foreach(['0-100' => 'R0–R100', '101-250' => 'R101–R250', '251-500' => 'R251–R500', '501-1000' => 'R501–R1,000', '1000+' => 'R1,000+'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('avg_appointment_value') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @php $bucketLabels = ['<1' => 'Less than 1 hour', '1-3' => '1–3 hours', '3-5' => '3–5 hours', '5-10' => '5–10 hours', '10+' => '10+ hours']; @endphp
            @foreach([
                'hours_booking_admin' => 'Hours per week managing appointments',
                'hours_availability_messages' => 'Hours per week answering "Are you available?" messages',
                'hours_manual_reminders' => 'Hours per week manually reminding clients',
            ] as $field => $label)
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ $label }}</label>
                    <select name="{{ $field }}" class="w-full border-slate-300 rounded-xl text-sm">
                        <option value="">Select…</option>
                        @foreach($bucketLabels as $value => $bucketLabel)
                            <option value="{{ $value }}" @selected(old($field) === $value)>{{ $bucketLabel }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Overall, how valuable has Xquisite been so far? *</label>
                <div class="flex items-center gap-4">
                    <span class="text-xs text-slate-400">Not valuable</span>
                    @for($i = 1; $i <= 5; $i++)
                        <label class="flex flex-col items-center gap-1 text-xs text-slate-400 cursor-pointer">
                            <input type="radio" name="value_rating" value="{{ $i }}" required @checked(old('value_rating') == $i) class="text-[#0078D4] focus:ring-[#0078D4]">
                            {{ $i }}
                        </label>
                    @endfor
                    <span class="text-xs text-slate-400">Very valuable</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">How likely are you to continue after the free period? *</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['very_likely' => 'Very likely', 'likely' => 'Likely', 'unsure' => 'Unsure', 'unlikely' => 'Unlikely', 'very_unlikely' => 'Very unlikely'] as $value => $label)
                        <label class="flex items-center gap-2 text-sm border border-slate-200 rounded-xl px-3 py-2 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50">
                            <input type="radio" name="continuation_likelihood" value="{{ $value }}" required @checked(old('continuation_likelihood') === $value) class="text-[#0078D4] focus:ring-[#0078D4]">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">What's the biggest change you've noticed?</label>
                <textarea name="biggest_change" rows="3" class="w-full border-slate-300 rounded-xl text-sm">{{ old('biggest_change') }}</textarea>
            </div>

            <label class="flex items-center gap-3 text-sm text-slate-700 border border-slate-200 rounded-xl px-4 py-3 cursor-pointer has-[:checked]:border-[#D4AF37] has-[:checked]:bg-amber-50">
                <input type="checkbox" name="would_recommend" value="1" @checked(old('would_recommend')) class="rounded text-[#D4AF37] focus:ring-[#D4AF37]">
                <span>I'd recommend Xquisite to another business owner</span>
            </label>

            <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl py-3 text-sm transition">
                Submit
            </button>
        </form>
    @endif
</main>

<footer class="border-t border-slate-200 mt-10 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>
</body>
</html>
