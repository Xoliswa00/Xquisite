<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-[#D4AF37]">New Service Agreement</h2>
            <a href="{{ route('service-agreements.index') }}" class="text-sm text-ink-muted hover:text-ink">&larr; Back to Agreements</a>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto p-6"
         x-data="{
             plans: @js($plans->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'monthly_fee' => $p->monthly_fee, 'minutes_allowance' => $p->minutes_allowance, 'features' => $p->features])),
             slaPlanId: '{{ old('sla_plan_id') }}',
             planName: '{{ old('plan_name') }}',
             monthlyFee: '{{ old('monthly_fee') }}',
             minutesAllowance: '{{ old('minutes_allowance', 30) }}',
             applyPlan() {
                 const p = this.plans.find(p => p.id == this.slaPlanId);
                 if (p) { this.planName = p.name; this.monthlyFee = p.monthly_fee; this.minutesAllowance = p.minutes_allowance; }
             },
             selectedFeatures() {
                 const p = this.plans.find(p => p.id == this.slaPlanId);
                 return p ? p.features : [];
             }
         }">
        <form method="POST" action="{{ route('service-agreements.store') }}" class="space-y-6">
            @csrf

            @if($errors->any())
                <div class="p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-xl text-sm">
                    <ul class="space-y-1">@foreach($errors->all() as $e)<li>• {{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-ink-muted">Client &amp; Service Type</h3>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Client *</label>
                    <select name="client_id" required class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                        <option value="">Select client...</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-ink-faint mt-1">No client yet? <a href="{{ route('clients.create') }}" class="text-[#0078D4]">Add one first</a>.</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Service Type *</label>
                    <select name="service_type" required class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                        @foreach(['website_hosting' => 'Website Hosting', 'pos_erp_support' => 'POS / ERP Support', 'automation_support' => 'Automation Support', 'reporting_support' => 'Reporting Support', 'general_support' => 'General Support', 'other' => 'Other'] as $key => $label)
                            <option value="{{ $key }}" @selected(old('service_type', 'website_hosting') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-ink-muted">SLA Plan</h3>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Start from a plan</label>
                    <select x-model="slaPlanId" @change="applyPlan()" name="sla_plan_id" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                        <option value="">— Custom (no plan) —</option>
                        <template x-for="p in plans" :key="p.id">
                            <option :value="p.id" x-text="p.name + ' — R' + p.monthly_fee + '/mo'"></option>
                        </template>
                    </select>
                    <ul class="mt-2 space-y-0.5" x-show="slaPlanId">
                        <template x-for="f in selectedFeatures()" :key="f">
                            <li class="text-xs text-ink-faint flex items-center gap-1.5">
                                <span class="w-1 h-1 rounded-full bg-[#D4AF37]"></span> <span x-text="f"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Plan Name (shown on contract) *</label>
                    <input type="text" name="plan_name" x-model="planName" required placeholder="e.g. Business Hosting & Maintenance"
                           class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Monthly Fee (R) *</label>
                        <input type="number" name="monthly_fee" x-model="monthlyFee" step="0.01" min="0" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Minutes Allowance / Month *</label>
                        <input type="number" name="minutes_allowance" x-model="minutesAllowance" min="0" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                </div>
            </div>

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-ink-muted">Term &amp; Billing</h3>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Start Date *</label>
                        <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Commitment (months) *</label>
                        <input type="number" name="commitment_months" value="{{ old('commitment_months', 12) }}" min="0" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-faint mb-1">Billing Day *</label>
                        <input type="number" name="billing_day" value="{{ old('billing_day', 1) }}" min="1" max="28" required
                               class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-ink-faint mb-1">Reactivation Fee (R)</label>
                    <input type="number" name="reactivation_fee" value="{{ old('reactivation_fee', 350) }}" step="0.01" min="0"
                           class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">
                </div>
            </div>

            <div class="bg-panel-2 rounded-xl p-6 space-y-4">
                <h3 class="text-sm font-semibold text-ink-muted">Notes</h3>
                <textarea name="notes" rows="3" class="w-full bg-app border-line-2 text-ink rounded-lg text-sm px-3 py-2">{{ old('notes') }}</textarea>
            </div>

            <div class="bg-panel rounded-xl p-4 text-xs text-ink-faint border border-line-2">
                Creating an agreement activates it immediately, generates the first month's charge, and creates a billing subscription.
            </div>

            <div class="flex justify-between">
                <a href="{{ route('service-agreements.index') }}" class="px-5 py-2 bg-panel-2 hover:bg-line-2 text-ink-muted rounded-lg text-sm">Cancel</a>
                <button type="submit" class="px-5 py-2 bg-[#0078D4] hover:bg-[#002B5B] text-white rounded-lg text-sm font-semibold">Create Agreement</button>
            </div>
        </form>
    </div>
</x-app-layout>
