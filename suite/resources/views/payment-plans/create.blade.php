<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('payment-plans.index') }}" class="text-slate-400 hover:text-white">← Payment Plans</a>
            <span class="text-slate-600">/</span>
            <h2 class="text-xl font-semibold">New Payment Plan</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl py-8 px-4 sm:px-6 lg:px-8"
         x-data="{
             type: 'custom',
             totalAmount: 0,
             depositAmount: 0,
             remaining: 1,
             intervalDays: 30,
             depositDue: '',
             preview() {
                 if (!this.totalAmount || !this.depositAmount || !this.depositDue) return [];
                 const schedule = [];
                 schedule.push({ label: 'Deposit', amount: this.depositAmount, due: this.depositDue });
                 const balance = this.totalAmount - this.depositAmount;
                 const perInstalment = this.remaining > 0 ? (balance / this.remaining).toFixed(2) : balance.toFixed(2);
                 const n = Math.max(1, this.remaining);
                 let d = new Date(this.depositDue);
                 for (let i = 0; i < n; i++) {
                     d.setDate(d.getDate() + parseInt(this.intervalDays));
                     schedule.push({ label: n === 1 ? 'Balance' : 'Instalment ' + (i+1), amount: +perInstalment, due: d.toISOString().slice(0,10) });
                 }
                 return schedule;
             }
         }">

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-900/30 border border-red-700 text-red-300 rounded-lg text-sm">
                @foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('payment-plans.store') }}" class="bg-slate-800 rounded-xl p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Plan Title <span class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required
                       placeholder="e.g. Layby – Blue Shweshwe 5m or Wedding 14 Feb Catering"
                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Customer</label>
                    <select name="customer_id"
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <option value="">— Walk-in / No customer —</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Type</label>
                    <select name="type" x-model="type"
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <option value="layby">Layby (retail)</option>
                        <option value="event_deposit">Event Deposit (catering/decor)</option>
                        <option value="quote_deposit">Quote Deposit</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Total Amount (R) <span class="text-red-400">*</span></label>
                    <input type="number" name="total_amount" min="1" step="0.01" required
                           value="{{ old('total_amount') }}" x-model.number="totalAmount"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Cancellation Fee (R)</label>
                    <input type="number" name="cancellation_fee" min="0" step="0.01"
                           value="{{ old('cancellation_fee', 0) }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    <p class="text-xs text-slate-500 mt-0.5">Kept if client cancels (layby/events)</p>
                </div>
            </div>

            <div class="border-t border-slate-700 pt-4">
                <p class="text-sm font-medium text-slate-300 mb-3">Payment Schedule</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Deposit Amount (R) <span class="text-red-400">*</span></label>
                        <input type="number" name="deposit_amount" min="0" step="0.01" required
                               value="{{ old('deposit_amount') }}" x-model.number="depositAmount"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Deposit Due Date <span class="text-red-400">*</span></label>
                        <input type="date" name="deposit_due" required
                               value="{{ old('deposit_due', today()->toDateString()) }}"
                               x-model="depositDue"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Remaining Instalments</label>
                        <input type="number" name="remaining_installments" min="0" max="24"
                               value="{{ old('remaining_installments', 1) }}" x-model.number="remaining"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        <p class="text-xs text-slate-500 mt-0.5">0 = deposit + 1 balance only</p>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1">Days Between Payments</label>
                        <input type="number" name="interval_days" min="1"
                               value="{{ old('interval_days', 30) }}" x-model.number="intervalDays"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                    </div>
                </div>

                {{-- Live schedule preview --}}
                <div class="mt-4 space-y-1.5" x-show="preview().length > 0">
                    <p class="text-xs text-slate-500 uppercase tracking-wide mb-2">Schedule preview</p>
                    <template x-for="(row, i) in preview()" :key="i">
                        <div class="flex items-center justify-between text-sm bg-slate-700/50 rounded-lg px-3 py-2">
                            <span class="text-slate-300" x-text="row.label"></span>
                            <span class="text-slate-400 text-xs" x-text="row.due"></span>
                            <span class="text-white font-medium" x-text="'R ' + (+row.amount).toFixed(2)"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                <textarea name="notes" rows="2"
                          class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] resize-none">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-6 py-2 rounded-lg font-medium">
                    Create Plan
                </button>
                <a href="{{ route('payment-plans.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
