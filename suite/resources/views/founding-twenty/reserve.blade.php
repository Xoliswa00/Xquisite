<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve Your Spot — Xquisite Creations Founding 20</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-800">

<header class="bg-white border-b border-slate-200 shadow-sm">
    <div class="max-w-2xl mx-auto px-4 py-4 flex items-center gap-3">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="w-9 h-9 rounded-lg object-contain shrink-0">
        <div class="min-w-0">
            <span class="block text-lg font-bold text-slate-900 truncate">Xquisite Creations</span>
            <span class="text-xs font-semibold uppercase tracking-wide text-[#D4AF37]">Founding 20 Programme</span>
        </div>
    </div>
</header>

<main class="max-w-md mx-auto px-4 py-12">
    <div class="mb-6 text-center">
        <div class="w-14 h-14 rounded-2xl bg-amber-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-[#D4AF37]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h1 class="text-xl font-bold text-slate-900">{{ $application->business_name }}, you're in</h1>
        <p class="text-slate-500 text-sm mt-2">
            To hold your spot in the Founding 20 Programme, we ask for a small, fully refundable reservation deposit.
            It's returned in full — this just confirms you're serious about actively using the platform for the 3 free months.
        </p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
            <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
        <dl class="grid grid-cols-2 gap-y-2 text-sm">
            <dt class="text-slate-500">Deposit amount</dt>
            <dd class="text-slate-900 font-semibold text-right">R{{ number_format($application->deposit_amount, 2) }}</dd>
            <dt class="text-slate-500">Reference</dt>
            <dd class="text-slate-900 font-mono text-right">{{ $application->deposit_reference }}</dd>
        </dl>

        <p class="text-sm text-slate-500 border-t border-slate-100 pt-4">
            We'll send you our banking details via {{ $application->preferred_contact_method }} — use the reference above
            when you pay, then upload your proof of payment below.
        </p>

        @if($application->deposit_submitted_at)
            <div class="p-3 bg-blue-50 border border-blue-100 text-blue-700 rounded-xl text-sm">
                Proof of payment received {{ $application->deposit_submitted_at->diffForHumans() }} — we'll confirm your spot shortly.
                You can upload a new file below if you need to replace it.
            </div>
        @endif

        <form method="POST" action="{{ route('founding-twenty.reserve.store', [$application, $token]) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Proof of payment</label>
                <input type="file" name="proof_of_payment" required accept=".jpg,.jpeg,.png,.heic,.heif,.webp,.pdf" class="w-full text-sm border-slate-300 rounded-xl">
            </div>
            <button type="submit" class="w-full bg-[#0078D4] hover:bg-[#0065B8] text-white font-semibold rounded-xl py-3 text-sm transition">
                Upload proof of payment
            </button>
        </form>
    </div>
</main>

<footer class="border-t border-slate-200 mt-10 py-6 text-center text-xs text-slate-400">
    <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 hover:opacity-80 transition-opacity">
        <img src="/img/android-icon-96x96.png" alt="Xquisite Creations" class="h-5 w-5 object-contain rounded">
        <span>Powered by <span class="font-semibold text-slate-500">Xquisite Creations</span></span>
    </a>
</footer>
</body>
</html>
