<x-app-layout>
    <x-slot name="header">Billing Settings</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.billing.index') }}" class="text-sm text-slate-400 hover:text-white transition-colors">← Platform Billing</a>
            <span class="text-slate-600">/</span>
            <h1 class="text-xl font-bold text-[#D4AF37]">Billing Settings</h1>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 rounded-xl text-sm">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl text-sm">
                Please fix the errors below before saving.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.billing.settings.save') }}">
            @csrf

            <div class="bg-slate-800 border border-slate-700 rounded-2xl divide-y divide-slate-700">

                {{-- Auto-billing toggle --}}
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-[#D4AF37] mb-4">Auto-Billing</h3>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="auto_billing_enabled" value="1"
                               {{ ($settings['auto_billing_enabled'] ?? '1') === '1' ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-slate-600 bg-slate-700 text-[#0078D4] focus:ring-[#0078D4] focus:ring-offset-slate-800">
                        <div>
                            <p class="text-sm font-medium text-white">Enable automatic monthly invoice generation</p>
                            <p class="text-xs text-slate-400 mt-0.5">When enabled, invoices are generated on the billing day each month via the scheduler.</p>
                        </div>
                    </label>
                </div>

                {{-- Timing settings --}}
                <div class="px-6 py-5 space-y-5">
                    <h3 class="text-sm font-semibold text-[#D4AF37]">Timing</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">
                                Billing Day of Month
                            </label>
                            <input type="number" name="billing_day_of_month"
                                   value="{{ $settings['billing_day_of_month'] ?? 1 }}"
                                   min="1" max="28"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
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
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            <p class="text-xs text-slate-500 mt-1">Days after invoice creation before it becomes overdue.</p>
                            @error('invoice_due_days')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Grace period --}}
                <div class="px-6 py-5">
                    <h3 class="text-sm font-semibold text-[#D4AF37] mb-4">Grace Period</h3>
                    <div class="max-w-xs">
                        <label class="block text-xs font-medium text-slate-300 mb-1.5">
                            Grace Period Days
                        </label>
                        <input type="number" name="grace_period_days"
                               value="{{ $settings['grace_period_days'] ?? 5 }}"
                               min="1" max="30"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <p class="text-xs text-slate-500 mt-1">Days a tenant has to pay after an invoice goes overdue before suspension.</p>
                        @error('grace_period_days')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Company details --}}
                <div class="px-6 py-5 space-y-4">
                    <h3 class="text-sm font-semibold text-[#D4AF37]">Company Details (Invoice Header)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Company Name</label>
                            <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" class="w-full bg-slate-700 border @error('company_name') border-red-500 @else border-slate-600 @enderror text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]" placeholder="Xquisite Creations (Pty) Ltd">
                            @error('company_name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">VAT Number</label>
                            <input type="text" name="company_vat" value="{{ old('company_vat', $settings['company_vat'] ?? '') }}" class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]" placeholder="4123456789">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Email</label>
                            <input type="email" name="company_email" value="{{ old('company_email', $settings['company_email'] ?? '') }}" class="w-full bg-slate-700 border @error('company_email') border-red-500 @else border-slate-600 @enderror text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]" placeholder="billing@xquisite.co.za">
                            @error('company_email') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Phone</label>
                            <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]" placeholder="+27 11 000 0000">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Address</label>
                            <textarea name="company_address" rows="2" class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]" placeholder="123 Main Street, Sandton, Johannesburg, 2196">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Contact & support channels --}}
                <div class="px-6 py-5 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-[#D4AF37]">Contact &amp; Support Channels</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Powers the WhatsApp chat button, demo banner, and welcome emails across the platform.</p>
                        </div>
                        <a href="{{ route('admin.team-members.index') }}"
                           class="text-xs text-[#0078D4] hover:text-[#0065B8] transition-colors whitespace-nowrap">
                            Manage Team →
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">
                                WhatsApp Number
                                <span class="text-slate-500 font-normal ml-1">— digits only, with country code</span>
                            </label>
                            <input type="text" name="whatsapp_number"
                                   value="{{ $settings['whatsapp_number'] ?? '' }}"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#25D366]"
                                   placeholder="27821234567">
                            <p class="text-xs text-slate-500 mt-1">No +, spaces, or dashes. e.g. 27821234567 for +27 82 123 4567</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">WhatsApp Pre-filled Message</label>
                            <input type="text" name="whatsapp_message"
                                   value="{{ $settings['whatsapp_message'] ?? '' }}"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#25D366]"
                                   placeholder="Hi! I'd like to learn more about Xquisite.">
                        </div>
                    </div>
                    @php $waNum = $settings['whatsapp_number'] ?? null; @endphp
                    @if($waNum)
                        <div class="flex items-center gap-2 bg-[#25D366]/10 border border-[#25D366]/30 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 text-[#25D366] shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.128.557 4.127 1.528 5.856L.057 23.5l5.793-1.452A11.94 11.94 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-1.886 0-3.652-.497-5.18-1.362l-.371-.214-3.439.862.925-3.33-.234-.389A9.955 9.955 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                            </svg>
                            <span class="text-xs text-[#25D366]">Active — chat button links to +{{ $waNum }}</span>
                            <a href="https://wa.me/{{ $waNum }}" target="_blank" rel="noopener" class="ml-auto text-xs text-[#25D366]/70 hover:text-[#25D366] underline">Test</a>
                        </div>
                    @endif
                </div>

                {{-- Banking details --}}
                <div class="px-6 py-5 space-y-4">
                    <h3 class="text-sm font-semibold text-[#D4AF37]">Banking Details (EFT Payment Instructions)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Bank Name</label>
                            <input type="text" name="bank_name" value="{{ $settings['bank_name'] ?? '' }}" class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]" placeholder="First National Bank">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Account Name</label>
                            <input type="text" name="bank_account_name" value="{{ $settings['bank_account_name'] ?? '' }}" class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]" placeholder="Xquisite Creation (Pty) Ltd">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Account Number</label>
                            <input type="text" name="bank_account_number" value="{{ $settings['bank_account_number'] ?? '' }}" class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]" placeholder="62xxxxxxxxxx">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-300 mb-1.5">Branch Code</label>
                            <input type="text" name="bank_branch_code" value="{{ $settings['bank_branch_code'] ?? '' }}" class="w-full bg-slate-700 border border-slate-600 text-slate-100 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-[#0078D4]" placeholder="250655">
                        </div>
                    </div>
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-5">
                <a href="{{ route('admin.billing.index') }}"
                   class="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors">Cancel</a>
                <button type="submit"
                        class="px-5 py-2 bg-[#0078D4] hover:bg-[#0065B8] text-white text-sm font-medium rounded-lg transition-colors">
                    Save Settings
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
