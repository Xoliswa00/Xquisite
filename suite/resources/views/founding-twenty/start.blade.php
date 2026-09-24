<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Founding 20 — Xquisite Creations</title>
    <meta name="description" content="Apply for the Xquisite Creations Founding 20 Programme: 3 months free, no setup fee. Step 1 takes about a minute.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-xl mx-auto px-4 py-3 flex items-center gap-3">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="w-9 h-9 rounded-lg object-contain shrink-0">
        <div class="min-w-0 flex-1">
            <span class="block text-base font-bold text-slate-900 truncate">Xquisite Creations</span>
            <span class="text-xs font-semibold uppercase tracking-wide text-[#D4AF37]">Founding 20 Programme</span>
        </div>
        <p class="text-xs font-medium text-slate-500 shrink-0">Step 1 of 2</p>
    </div>
    <div class="h-1 bg-slate-100" aria-hidden="true"><div class="h-1 bg-[#0078D4]" style="width:50%"></div></div>
</header>

<main class="max-w-xl mx-auto px-4 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">First, tell us who you are</h1>
        <p class="text-slate-500 text-base mt-2 leading-relaxed">
            This takes about a minute and saves your place, so you can carry on later if you're interrupted.
            Applying costs nothing. If you're selected, you'll hold your spot with a fully refundable R100 deposit, and only then.
        </p>
        @if($referredByTenantId)
            <p class="text-sm text-[#D4AF37] font-medium mt-2">You're applying via a referral. Thanks for spreading the word!</p>
        @endif
    </div>

    @if($errors->any())
        <div id="form-errors" role="alert" class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <p class="font-semibold mb-1">A few things need another look. Everything else you entered has been kept.</p>
            <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('founding-twenty.store') }}" class="bg-white rounded-2xl border border-slate-200 p-6 space-y-5">
        @csrf
        <input type="hidden" name="source" value="{{ old('source', $source) }}">
        <input type="hidden" name="outreach_campaign_id" value="{{ old('outreach_campaign_id', $campaignId ?: '') }}">
        <input type="hidden" name="referred_by_tenant_id" value="{{ old('referred_by_tenant_id', $referredByTenantId ?: '') }}">

        <div>
            <label for="owner_name" class="block text-sm font-medium text-slate-700 mb-1">Your name *</label>
            <input type="text" id="owner_name" name="owner_name" value="{{ old('owner_name') }}" required autocomplete="name"
                   class="w-full rounded-xl text-base sm:text-sm @error('owner_name') border-red-400 @else border-slate-300 @enderror">
            @error('owner_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="business_name" class="block text-sm font-medium text-slate-700 mb-1">Business name *</label>
            <input type="text" id="business_name" name="business_name" value="{{ old('business_name') }}" required autocomplete="organization"
                   class="w-full rounded-xl text-base sm:text-sm @error('business_name') border-red-400 @else border-slate-300 @enderror">
            @error('business_name')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <p class="block text-sm font-medium text-slate-700 mb-2">Your role *</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach(['owner' => 'I own it', 'manager' => 'I manage it', 'staff' => 'I work there', 'other' => 'Something else'] as $value => $label)
                    <label class="flex items-center gap-2 text-sm border rounded-xl px-3 py-3 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50 @error('applicant_role') border-red-400 @else border-slate-200 @enderror">
                        <input type="radio" name="applicant_role" value="{{ $value }}" required @checked(old('applicant_role') === $value) class="text-[#0078D4] focus:ring-[#0078D4]">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            @error('applicant_role')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">Phone (WhatsApp) *</label>
            <input type="tel" inputmode="tel" id="phone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="082 123 4567"
                   class="w-full rounded-xl text-base sm:text-sm @error('phone') border-red-400 @else border-slate-300 @enderror">
            @error('phone')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <p class="block text-sm font-medium text-slate-700 mb-2">How should we reach you? *</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach(['whatsapp' => 'WhatsApp', 'call' => 'Phone call', 'email' => 'Email'] as $value => $label)
                    <label class="flex items-center justify-center text-center gap-2 text-sm border border-slate-200 rounded-xl px-2 py-3 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50">
                        <input type="radio" name="preferred_contact_method" value="{{ $value }}" required @checked(old('preferred_contact_method', 'whatsapp') === $value) class="sr-only">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email <span id="email-optional" class="font-normal text-slate-400">(optional)</span></label>
            <input type="email" inputmode="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email"
                   class="w-full rounded-xl text-base sm:text-sm @error('email') border-red-400 @else border-slate-300 @enderror">
            @error('email')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="best_contact_time" class="block text-sm font-medium text-slate-700 mb-1">Best time to reach you <span class="font-normal text-slate-400">(optional)</span></label>
            <input type="text" id="best_contact_time" name="best_contact_time" value="{{ old('best_contact_time') }}" placeholder="e.g. weekday mornings"
                   class="w-full border-slate-300 rounded-xl text-base sm:text-sm">
        </div>

        <div>
            <label for="why_founding_20" class="block text-sm font-medium text-slate-700 mb-1">Why would you like to be part of Founding 20? <span class="font-normal text-slate-400">(optional)</span></label>
            <textarea id="why_founding_20" name="why_founding_20" rows="3" maxlength="1500"
                      placeholder="A sentence or two is plenty. What would change for you if your bookings ran smoothly?"
                      class="w-full border-slate-300 rounded-xl text-base sm:text-sm">{{ old('why_founding_20') }}</textarea>
        </div>

        <div>
            <label for="heard_about_via" class="block text-sm font-medium text-slate-700 mb-1">How did you hear about this? <span class="font-normal text-slate-400">(optional)</span></label>
            <select id="heard_about_via" name="heard_about_via" class="w-full border-slate-300 rounded-xl text-base sm:text-sm">
                <option value="">Select…</option>
                @foreach(['tiktok' => 'TikTok', 'whatsapp' => 'WhatsApp', 'instagram_facebook' => 'Instagram or Facebook', 'friend' => 'A friend or another business owner', 'website' => 'The Xquisite website', 'other' => 'Somewhere else'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('heard_about_via') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <label class="flex items-start gap-3 text-sm text-slate-600 border border-slate-200 rounded-xl px-4 py-3 cursor-pointer has-[:checked]:border-[#0078D4] has-[:checked]:bg-blue-50">
            <input type="checkbox" name="privacy_consent" value="1" required @checked(old('privacy_consent')) class="mt-0.5 rounded text-[#0078D4] focus:ring-[#0078D4]">
            <span>I'm happy for Xquisite Creations to save my details and answers, and to contact me about this application and, if I'm selected, the Founding 20 Programme, as described in the <a href="{{ route('privacy') }}" target="_blank" class="text-[#0078D4] underline hover:no-underline">Privacy Policy</a>. *</span>
        </label>
        @error('privacy_consent')<p class="text-red-600 text-xs -mt-3">{{ $message }}</p>@enderror

        <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl py-3.5 text-base transition">
            Continue to the questions
        </button>
        <p class="text-center text-xs text-slate-400 -mt-2">Nothing is charged when you apply.</p>
    </form>
</main>

<footer class="border-t border-slate-200 mt-10 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>

<script>
(function () {
    // Email becomes required when it's how they want to be reached.
    var email = document.getElementById('email');
    var optional = document.getElementById('email-optional');
    var radios = document.querySelectorAll('input[name=preferred_contact_method]');
    function sync() {
        var chosen = document.querySelector('input[name=preferred_contact_method]:checked');
        var needed = chosen && chosen.value === 'email';
        email.required = needed;
        optional.textContent = needed ? '(needed, since you chose email)' : '(optional)';
    }
    radios.forEach(function (r) { r.addEventListener('change', sync); });
    sync();

    var err = document.getElementById('form-errors');
    if (err) { err.scrollIntoView({ block: 'center' }); }
})();
</script>
</body>
</html>
