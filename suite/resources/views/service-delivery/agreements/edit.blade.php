<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">Edit Agreement</h2>
            <a href="{{ route('service-agreements.show', $serviceAgreement) }}" class="text-sm text-ink-muted hover:text-ink">&larr; Back</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6">
        <form method="POST" action="{{ route('service-agreements.update', $serviceAgreement) }}" class="space-y-6">
            @csrf
            @method('PUT')

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <p class="text-xs text-ink-faint">Client: <span class="text-ink font-medium">{{ $serviceAgreement->client?->name }}</span> — service type and client can't be changed after creation.</p>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Plan Name *</label>
                    <input type="text" name="plan_name" value="{{ old('plan_name', $serviceAgreement->plan_name) }}" required
                           class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Monthly Fee (R) *</label>
                        <input type="number" name="monthly_fee" value="{{ old('monthly_fee', $serviceAgreement->monthly_fee) }}" step="0.01" min="0" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Minutes Allowance / Month *</label>
                        <input type="number" name="minutes_allowance" value="{{ old('minutes_allowance', $serviceAgreement->minutes_allowance) }}" min="0" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Commitment (months) *</label>
                        <input type="number" name="commitment_months" value="{{ old('commitment_months', $serviceAgreement->commitment_months) }}" min="0" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Billing Day *</label>
                        <input type="number" name="billing_day" value="{{ old('billing_day', $serviceAgreement->billing_day) }}" min="1" max="28" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Reactivation Fee (R)</label>
                    <input type="number" name="reactivation_fee" value="{{ old('reactivation_fee', $serviceAgreement->reactivation_fee) }}" step="0.01" min="0"
                           class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Notes</label>
                    <textarea name="notes" rows="3" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">{{ old('notes', $serviceAgreement->notes) }}</textarea>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('service-agreements.show', $serviceAgreement) }}" class="px-5 py-2 bg-panel-2 hover:bg-line-2 text-ink-muted rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white rounded-lg text-sm font-semibold">Save Changes</button>
            </div>
        </form>
    </div>
</x-app-layout>
