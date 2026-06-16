<x-app-layout>
    <x-slot name="header">Billing Settings</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.billing.index') }}" class="text-sm text-slate-400 hover:text-white transition-colors">← Platform Billing</a>
            <span class="text-slate-600">/</span>
            <h1 class="text-xl font-bold text-white">Billing Settings</h1>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.billing.settings.save') }}">
            @csrf

            <div class="bg-slate-800 border border-slate-700 rounded-2xl divide-y divide-slate-700">

                {{-- Auto-billing toggle --}}
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-white mb-4">Auto-Billing</h3>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_billing_enabled" value="1"
                               {{ ($settings['auto_billing_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-800">
                        <div>
                            <p class="text-sm font-medium text-white">Enable automatic monthly invoice generation</p>
                            <p class="text-xs text-slate-400 mt-0.5">When enabled, invoices are generated on the billing day each month via the scheduler.</p>
                        </div>
                    </label>
                </div>

                {{-- Timing settings --}}
                <div class="px-6 py-5 space-y-5">
                    <h3 class="text-sm font-semibold text-white">Timing</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">
                                Billing Day of Month
                            </label>
                            <input type="number" name="billing_day_of_month"
                                   value="{{ $settings['billing_day_of_month'] ?? 1 }}"
                                   min="1" max="28"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <p class="text-xs text-slate-500 mt-1">Day invoices are generated (1–28). Max 28 to avoid month-end issues.</p>
                            @error('billing_day_of_month')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">
                                Invoice Due Days
                            </label>
                            <input type="number" name="invoice_due_days"
                                   value="{{ $settings['invoice_due_days'] ?? 7 }}"
                                   min="1" max="60"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <p class="text-xs text-slate-500 mt-1">Days after invoice creation before it becomes overdue.</p>
                            @error('invoice_due_days')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Grace period --}}
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-white mb-4">Grace Period</h3>
                    <div class="max-w-xs">
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">
                            Grace Period Days
                        </label>
                        <input type="number" name="grace_period_days"
                               value="{{ $settings['grace_period_days'] ?? 5 }}"
                               min="1" max="30"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        <p class="text-xs text-slate-500 mt-1">Days a tenant has to pay after an invoice goes overdue before suspension.</p>
                        @error('grace_period_days')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Plan prices (read-only reference) --}}
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-white mb-1">Plan Prices</h3>
                    <p class="text-xs text-slate-400 mb-4">Configured in code — update <code class="text-slate-300">Tenant::planAmount()</code> to change.</p>
                    <div class="grid grid-cols-3 gap-3">
                        @foreach(['basic' => 'Basic', 'premium' => 'Premium', 'enterprise' => 'Enterprise'] as $key => $label)
                            <div class="bg-slate-900/50 border border-slate-700 rounded-lg px-4 py-3 text-center">
                                <p class="text-xs text-slate-400">{{ $label }}</p>
                                <p class="text-lg font-bold text-white mt-0.5">R{{ number_format(\App\Models\Tenant::planAmount($key), 0) }}</p>
                                <p class="text-xs text-slate-500">/mo</p>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-5">
                <a href="{{ route('admin.billing.index') }}"
                   class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-lg transition-colors">
                    Save Settings
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
