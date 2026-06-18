<x-app-layout>
    <x-slot name="header">Billing</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h2 class="text-xl font-bold text-[#D4AF37]">Platform Billing</h2>
            <p class="text-sm text-slate-400 mt-1">Your active modules and payment history.</p>
        </div>

        {{-- Suspended banner --}}
        @if($tenant->suspended_at)
            <div class="bg-red-900/40 border border-red-700 rounded-2xl px-5 py-4">
                <p class="font-semibold text-red-300">Your account is suspended</p>
                <p class="text-sm text-red-400 mt-1">Please pay your outstanding invoice to restore access.</p>
            </div>
        @endif

        {{-- Trial banner --}}
        @if($tenant->isOnTrial())
            @php
                $daysLeft       = (int) now()->diffInDays($tenant->trial_ends_at, false);
                $firstInvoiceOn = $tenant->trial_ends_at->copy()->addMonthNoOverflow()->startOfMonth();
            @endphp
            <div class="bg-[#D4AF37]/10 border border-[#D4AF37]/30 rounded-2xl px-5 py-4 space-y-3">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-[#D4AF37] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        <p class="text-[#D4AF37] text-sm font-medium">
                            Free trial — <strong>{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }}</strong> remaining
                        </p>
                        <p class="text-[#D4AF37]/70 text-xs mt-1">
                            Trial ends <strong>{{ $tenant->trial_ends_at->format('d F Y') }}</strong>.
                            Your first invoice will be generated on <strong>{{ $firstInvoiceOn->format('d F Y') }}</strong> and due 7 days later.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Grace countdown bar --}}
        @if($tenant->grace_period_ends_at && !$tenant->suspended_at)
            <div class="bg-amber-900/30 border border-amber-700 rounded-2xl px-5 py-4">
                @php $graceRemaining = 100 - $gracePercent; @endphp
                <div class="flex items-center justify-between mb-2">
                    <p class="font-semibold text-amber-300">Grace Period — {{ $tenant->graceDaysLeft() }} day(s) remaining</p>
                    <span class="text-sm text-amber-400">{{ $graceRemaining }}% time left</span>
                </div>
                <div class="h-2 bg-amber-900/50 rounded-full overflow-hidden">
                    <div class="h-full bg-amber-500 rounded-full transition-all" style="width: {{ $graceRemaining }}%"></div>
                </div>
                <p class="text-xs text-amber-400 mt-2">Pay your outstanding invoice to prevent suspension.</p>
            </div>
        @endif

        {{-- Active modules --}}
        @php
            $tenant->load('activeModules');
            $activeModules  = $tenant->tenantModules()->where('is_active', true)->with('platformModule')->get();
            $nextBillingOn  = now()->day === 1 ? now()->startOfMonth() : now()->addMonthNoOverflow()->startOfMonth();
            $nextDueOn      = $nextBillingOn->copy()->addDays(7);
        @endphp

        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-white">Active Modules</p>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if($tenant->isOnTrial())
                            Billing starts {{ $firstInvoiceOn->format('d M Y') }}
                        @else
                            Next invoice: <span class="text-slate-400">{{ $nextBillingOn->format('d M Y') }}</span> &middot; due <span class="text-slate-400">{{ $nextDueOn->format('d M Y') }}</span>
                        @endif
                    </p>
                </div>
                @php $badge = $tenant->billingStatusLabel(); @endphp
                <span class="px-3 py-1.5 rounded-full text-xs font-semibold border {{ $tenant->billingStatusClass() }}">{{ $badge }}</span>
            </div>

            @if($activeModules->isEmpty())
                <div class="px-5 py-8 text-center">
                    <p class="text-slate-400 text-sm">No modules active yet.</p>
                    <a href="{{ route('settings.modules.index') }}" class="mt-3 inline-block text-xs text-[#0078D4] hover:text-[#0065B8]">Activate a module →</a>
                </div>
            @else
                @foreach($activeModules as $tm)
                    <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-800/60 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $tm->platformModule?->name ?? ucfirst(str_replace('_', ' ', $tm->module)) }}</p>
                            @if($tm->activated_at)
                                <p class="text-xs text-slate-500 mt-0.5">Active since {{ $tm->activated_at->format('d M Y') }}</p>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-white">R{{ number_format($tm->monthly_price, 2) }}<span class="text-xs text-slate-500 font-normal">/mo</span></p>
                    </div>
                @endforeach
                <div class="flex items-center justify-between px-5 py-4 bg-slate-800/40 border-t border-slate-700">
                    <p class="text-sm font-semibold text-slate-300">Monthly total</p>
                    <p class="text-lg font-bold text-[#D4AF37]">R{{ number_format($tenant->monthlyTotal(), 2) }}</p>
                </div>
            @endif
        </div>

        {{-- Outstanding invoices --}}
        @if($unpaid->count())
            <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-800">
                    <p class="font-semibold text-white">Outstanding Invoices</p>
                </div>
                @foreach($unpaid as $inv)
                    @php $badge = $inv->status_badge; @endphp
                    <div class="px-5 py-4 border-b border-slate-800 last:border-0 space-y-3">
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <p class="font-medium text-white">{{ $inv->invoice_number }}</p>
                                <p class="text-sm text-slate-400">Due {{ $inv->due_date->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-white">R{{ number_format($inv->amount, 2) }}</p>
                                <span class="text-xs px-2 py-0.5 rounded-full border {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                            </div>
                            <a href="{{ route('billing.pdf', $inv) }}" class="text-sm px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 rounded-lg transition-colors">PDF</a>
                        </div>

                        {{-- POP upload / submitted state --}}
                        @if($inv->isAwaitingConfirmation())
                            <div class="flex items-center gap-3 bg-[#0078D4]/10 border border-[#0078D4]/30 rounded-xl px-4 py-3">
                                <svg class="w-5 h-5 text-[#0078D4] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-[#0078D4]">Proof of payment submitted</p>
                                    <p class="text-xs text-[#0078D4]/70 mt-0.5">Uploaded {{ $inv->pop_uploaded_at->format('d M Y \a\t H:i') }} · We will confirm within 1–2 business days.</p>
                                </div>
                                <a href="{{ route('billing.pop.download', $inv) }}" class="text-xs text-[#0078D4] hover:text-[#0065B8] underline whitespace-nowrap">View file</a>
                            </div>
                        @else
                            <div class="bg-slate-800/50 border border-slate-700 rounded-xl px-4 py-3">
                                <p class="text-xs text-slate-400 mb-2">Paid via EFT? Upload your bank proof of payment.</p>
                                <form method="POST" action="{{ route('billing.pop.upload', $inv) }}" enctype="multipart/form-data" class="flex items-center gap-3">
                                    @csrf
                                    <label class="flex-1 cursor-pointer">
                                        <input type="file" name="pop_file" accept=".pdf,.jpg,.jpeg,.png" required class="block w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-[#0078D4]/20 file:text-[#0078D4] hover:file:bg-[#0078D4]/30 cursor-pointer">
                                    </label>
                                    <button type="submit" class="shrink-0 text-xs px-4 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white rounded-lg transition-colors">Submit POP</button>
                                </form>
                                <p class="text-xs text-slate-500 mt-1.5">PDF, JPG or PNG · Max 5 MB</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Invoice history --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-800">
                <p class="font-semibold text-white">Invoice History</p>
            </div>
            @forelse($invoices as $inv)
                <div class="flex items-center px-5 py-4 border-b border-slate-800 last:border-0 hover:bg-slate-800/30">
                    <div class="flex-1">
                        <a href="{{ route('billing.show', $inv) }}" class="font-medium text-white hover:text-[#0078D4]">{{ $inv->invoice_number }}</a>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $inv->billing_period_start->format('d M') }} – {{ $inv->billing_period_end->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-white">R{{ number_format($inv->amount, 2) }}</p>
                        @php $badge = $inv->status_badge; @endphp
                        <span class="text-xs px-2 py-0.5 rounded-full border {{ $badge['class'] }}">{{ $badge['label'] }}</span>
                    </div>
                </div>
            @empty
                <div class="px-5 py-8 text-center">
                    <p class="text-slate-400 text-sm">No invoices yet.</p>
                    @if($tenant->isOnTrial())
                        @php $firstInvoiceOn = $tenant->trial_ends_at->copy()->addMonthNoOverflow()->startOfMonth(); @endphp
                        <p class="text-slate-500 text-xs mt-2">Your first invoice will be generated on <strong class="text-slate-400">{{ $firstInvoiceOn->format('d F Y') }}</strong> once your free trial ends.</p>
                    @else
                        <p class="text-slate-500 text-xs mt-2">Invoices are generated on the 1st of each month.</p>
                    @endif
                </div>
            @endforelse
        </div>

        {{ $invoices->links() }}

        {{-- Billing information (for invoice header) --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800">
                <p class="font-semibold text-white">Billing Information</p>
                <p class="text-xs text-slate-500 mt-0.5">This information appears on your invoices and PDF documents.</p>
            </div>
            <form method="POST" action="{{ route('billing.info.update') }}" class="px-5 py-4 space-y-4">
                @csrf
                @method('PATCH')

                @if(session('success'))
                    <div class="px-4 py-2.5 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs rounded-lg">{{ session('success') }}</div>
                @endif

                <div>
                    <x-input-label for="address" value="Business Address" />
                    <textarea id="address" name="address" rows="3"
                        class="mt-1 block w-full bg-slate-900 border-slate-600 text-white placeholder-slate-500 focus:border-[#0078D4] focus:ring-[#0078D4] rounded-md shadow-sm text-sm"
                        placeholder="123 Main Street, Sandton, Johannesburg, 2196">{{ old('address', $tenant->address) }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-1" />
                </div>

                <div class="max-w-xs">
                    <x-input-label for="vat_number" value="VAT Number (optional)" />
                    <x-text-input id="vat_number" name="vat_number" type="text" class="mt-1 block w-full text-sm"
                        :value="old('vat_number', $tenant->vat_number)"
                        placeholder="4123456789" />
                    <x-input-error :messages="$errors->get('vat_number')" class="mt-1" />
                </div>

                <div class="flex justify-end">
                    <x-primary-button>Save</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
