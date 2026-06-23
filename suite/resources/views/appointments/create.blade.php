<x-app-layout>
    <x-slot name="header">New Booking</x-slot>

    <div class="max-w-2xl">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('appointments.store') }}" class="space-y-5"
                  x-data="bookingForm(
                      @js($combos),
                      @js($services->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'duration_minutes' => $s->duration_minutes, 'price' => (float)$s->price])->values()),
                      @js(array_map('intval', old('service_ids', [])))
                  )">
                @csrf

                {{-- Customer --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Customer</label>
                    <select name="customer_id" x-model="customerId" required
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('customer_id') border-red-500 @enderror">
                        <option value="">Select customer…</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                {{-- Combo Deal --}}
                @if($combos->isNotEmpty())
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">
                        Combo Deal
                        <span class="text-slate-500 font-normal text-xs ml-1">(optional)</span>
                    </label>
                    <select name="combo_id" x-model="selectedComboId" @change="onComboChange()"
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('combo_id') border-red-500 @enderror">
                        <option value="">— No combo —</option>
                        @foreach($combos as $combo)
                            <option value="{{ $combo['id'] }}" @selected(old('combo_id') == $combo['id'])>
                                {{ $combo['name'] }} — R{{ number_format($combo['combo_price'], 2) }}
                                (save R{{ number_format($combo['savings'], 2) }})
                            </option>
                        @endforeach
                    </select>
                    @error('combo_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror

                    <div x-show="selectedComboId" x-cloak
                         class="mt-2 rounded-lg bg-emerald-900/20 border border-emerald-800/40 px-3 py-2">
                        <p class="text-xs font-medium text-emerald-400 mb-0.5">
                            Includes: <span x-text="getCombo()?.service_names.join(', ')"></span>
                        </p>
                        <p class="text-xs text-emerald-400/70">
                            Combo price: R<span x-text="getCombo()?.combo_price.toFixed(2)"></span>
                            <span class="text-emerald-600 ml-1">(saving R<span x-text="getCombo()?.savings.toFixed(2)"></span>)</span>
                        </p>
                    </div>
                </div>
                @endif

                {{-- Services — filter-style dropdown (Creative Tim pattern) --}}
                <div class="relative" @keydown.escape="svcOpen = false">
                    <label class="block text-sm font-medium text-slate-300 mb-1">
                        Services <span class="text-red-400">*</span>
                    </label>

                    {{-- Trigger button --}}
                    <button type="button" @click="openSvc()"
                            class="w-full flex items-center justify-between gap-2 px-4 py-2.5 rounded-lg border text-sm font-medium transition-colors
                                   @error('service_ids') border-red-500 text-red-400 bg-red-900/10
                                   @else border-slate-600 bg-slate-700 text-slate-200 hover:bg-slate-600 hover:border-slate-500 @enderror"
                            :class="svcOpen ? '!border-[#0078D4] ring-1 ring-[#0078D4]' : ''">
                        <span x-show="selectedServiceIds.length === 0" class="text-slate-400 font-normal">Select services…</span>
                        <span x-show="selectedServiceIds.length > 0" x-cloak>
                            <span x-text="selectedServiceIds.length"></span>
                            <span x-text="selectedServiceIds.length === 1 ? ' service selected' : ' services selected'"></span>
                        </span>
                        <svg class="shrink-0 w-4 h-4 text-slate-400 transition-transform" :class="svcOpen ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    {{-- Selected chips (shown below button when closed) --}}
                    <div x-show="!svcOpen && selectedServiceIds.length > 0" x-cloak
                         class="flex flex-wrap gap-1.5 mt-2">
                        <template x-for="svc in selectedServices" :key="svc.id">
                            <span class="inline-flex items-center gap-1 bg-[#001A3A] border border-[#002B5B] text-[#B8D4F0] text-xs font-medium px-2.5 py-1 rounded-full">
                                <span x-text="svc.name"></span>
                                <button type="button" @click="removeService(svc.id)"
                                        class="ml-0.5 text-[#6EA8D4] hover:text-white leading-none">&times;</button>
                            </span>
                        </template>
                    </div>

                    {{-- Dropdown panel --}}
                    <div x-show="svcOpen" x-cloak
                         @click.outside="svcOpen = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute z-50 left-0 right-0 mt-1 bg-slate-800 border border-slate-600 rounded-xl shadow-2xl overflow-hidden origin-top">

                        {{-- Search input --}}
                        <div class="p-3 border-b border-slate-700">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input type="text" x-ref="svcSearch" x-model="svcSearch"
                                       placeholder="Search services…"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg pl-9 pr-3 py-2 focus:outline-none focus:border-[#0078D4] placeholder-slate-500">
                            </div>
                        </div>

                        {{-- Service list --}}
                        <div class="overflow-y-scroll h-64 sm:h-80 p-2">
                            <template x-for="(item, idx) in groupedServiceList" :key="item._header ? 'h-' + idx : item.id">
                                <div>
                                    {{-- Category header --}}
                                    <p x-show="item._header"
                                       x-text="item.label"
                                       class="px-2 pt-3 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500"></p>

                                    {{-- Service row --}}
                                    <button x-show="!item._header"
                                            type="button" @click="toggleService(item.id)"
                                            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg cursor-pointer transition-colors text-left"
                                            :class="isSelected(item.id) ? 'bg-[#0078D4]/15 hover:bg-[#0078D4]/25' : 'hover:bg-slate-700/70'">

                                        <span class="shrink-0 w-2 h-2 rounded-full mt-0.5 transition-colors"
                                              :class="isSelected(item.id) ? 'bg-[#0078D4]' : 'bg-slate-600'"></span>

                                        <span class="flex-1 min-w-0">
                                            <span class="block text-sm font-medium truncate"
                                                  :class="isSelected(item.id) ? 'text-slate-100' : 'text-slate-300'"
                                                  x-text="item.name"></span>
                                            <span class="block text-xs text-slate-500 mt-0.5">
                                                <span x-text="fmtDur(item.duration_minutes)"></span>
                                                &nbsp;·&nbsp;
                                                R<span x-text="parseFloat(item.price).toFixed(2)"></span>
                                            </span>
                                        </span>

                                        <svg x-show="isSelected(item.id)" class="shrink-0 w-4 h-4 text-[#0078D4]" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>

                            <p x-show="filteredServices.length === 0"
                               class="px-3 py-4 text-sm text-slate-500 text-center">
                                No services match "<span x-text="svcSearch"></span>"
                            </p>
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-between px-4 py-2.5 border-t border-slate-700 bg-slate-900/50">
                            <span class="text-xs text-slate-500">
                                <span x-text="selectedServiceIds.length"></span> of
                                <span x-text="allServices.length"></span> selected
                            </span>
                            <button type="button" @click="svcOpen = false"
                                    class="text-xs font-semibold text-[#0078D4] hover:text-[#B8D4F0] transition-colors">
                                Done ✓
                            </button>
                        </div>
                    </div>

                    {{-- Hidden inputs for form submission --}}
                    <template x-for="id in selectedServiceIds" :key="id">
                        <input type="hidden" name="service_ids[]" :value="id">
                    </template>

                    @error('service_ids')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    @error('service_ids.*')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                {{-- Staff + Status --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">
                            Staff
                            <span class="text-slate-500 font-normal text-xs ml-1">(optional)</span>
                        </label>
                        <select name="staff_id"
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('staff_id') border-red-500 @enderror">
                            <option value="">— Unassigned —</option>
                            @foreach($staff as $m)
                                <option value="{{ $m->id }}" @selected(old('staff_id') == $m->id)>{{ $m->name }}</option>
                            @endforeach
                        </select>
                        @error('staff_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Status</label>
                        <select name="status" class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            @foreach(['pending','confirmed','completed','cancelled','no_show','tentative'] as $s)
                                <option value="{{ $s }}" @selected(old('status', 'pending') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Duration / price summary --}}
                <div x-show="selectedServiceIds.length > 0" x-cloak
                     class="rounded-lg bg-slate-700/50 border border-slate-600 px-4 py-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-400">Total duration</span>
                        <span class="text-slate-100 font-medium" x-text="summaryDuration"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-1">
                        <span class="text-slate-400">Services total</span>
                        <span class="text-slate-300 font-medium" x-text="'R' + summaryPrice"></span>
                    </div>
                    <template x-if="selectedComboId">
                        <div class="border-t border-slate-600 mt-2 pt-2 flex items-center justify-between text-sm">
                            <span class="text-emerald-400">Combo deal price</span>
                            <span class="text-emerald-400 font-semibold">
                                R<span x-text="getCombo()?.combo_price.toFixed(2)"></span>
                                <span class="text-xs font-normal text-emerald-600 ml-1">+ extras</span>
                            </span>
                        </div>
                    </template>
                </div>

                {{-- Client Intelligence Banner --}}
                <div x-show="intelBanner && !intelDismissed" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="rounded-xl bg-[#001A3A]/60 border border-[#0078D4]/40 px-4 py-3">
                    <div class="flex items-start gap-3">
                        <svg class="shrink-0 w-4 h-4 text-[#0078D4] mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-[#B8D4F0]">
                                Client timing insight
                                <span class="font-normal text-xs text-slate-400 ml-1"
                                      x-text="intelBanner ? '(' + intelBanner.booking_count + ' past bookings)' : ''"></span>
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                <span x-text="intelBanner?.customer_name ?? ''"></span> typically takes
                                <span class="text-[#B8D4F0] font-medium"
                                      x-text="intelBanner ? fmtDur(intelBanner.avg_actual) : ''"></span>
                                for similar services
                                <template x-if="intelBanner && intelBanner.avg_actual !== intelBanner.avg_booked">
                                    <span> — estimated booking is
                                        <span x-text="fmtDur(intelBanner?.avg_booked ?? 0)"></span>
                                    </span>
                                </template>
                            </p>
                            <div x-show="durationOverride" class="mt-1.5 text-xs text-emerald-400 font-medium">
                                ✓ Duration set to <span x-text="durationOverride ? fmtDur(durationOverride) : ''"></span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" @click="acceptSuggestion()"
                                    class="text-xs px-3 py-1.5 bg-[#0078D4] hover:bg-[#0078D4]/80 text-white rounded-lg font-medium transition-colors"
                                    x-text="intelBanner ? 'Use ' + fmtDur(intelBanner.avg_actual) : ''">
                            </button>
                            <button type="button" @click="dismissIntel()"
                                    class="text-base leading-none text-slate-500 hover:text-slate-300 transition-colors px-1">&times;</button>
                        </div>
                    </div>
                </div>

                {{-- Hidden duration override passed to store() when tenant accepts the suggestion --}}
                <input type="hidden" name="duration_override" :value="durationOverride || ''">

                {{-- Date & Time --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" required
                           value="{{ old('scheduled_at') }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] @error('scheduled_at') border-red-500 @enderror">
                    @error('scheduled_at')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]"
                              placeholder="Optional notes…">{{ old('notes') }}</textarea>
                </div>

                {{-- Event Brief --}}
                <div x-data="{ open: {{ old('headcount') || old('venue') || old('event_type') ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                            class="flex items-center gap-2 text-sm text-[#0078D4] hover:text-[#B8D4F0] transition">
                        <svg class="w-4 h-4 transition" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                        Event Brief
                        <span class="text-slate-500 font-normal">(catering, decor & events)</span>
                    </button>

                    <div x-show="open" x-transition class="mt-4 space-y-4 border-t border-slate-700 pt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Guest Count / Headcount</label>
                                <input type="number" name="headcount" min="1" value="{{ old('headcount') }}"
                                       placeholder="e.g. 80"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Event Type</label>
                                <select name="event_type"
                                        class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                    <option value="">— Select type —</option>
                                    @foreach(['Wedding','Corporate Function','Birthday','Private Party','Funeral','Product Launch','Year End Function','Other'] as $t)
                                        <option value="{{ $t }}" @selected(old('event_type') === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Venue / Location</label>
                            <input type="text" name="venue" value="{{ old('venue') }}"
                                   placeholder="e.g. Ballroom, 15 Oak Street, Sandton"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Setup Time</label>
                                <input type="datetime-local" name="setup_at" value="{{ old('setup_at') }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Breakdown / Collection Time</label>
                                <input type="datetime-local" name="breakdown_at" value="{{ old('breakdown_at') }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Dietary Requirements</label>
                            <textarea name="dietary_notes" rows="2"
                                      placeholder="e.g. 10 vegetarian, 5 halaal, 2 nut allergy…"
                                      class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] resize-none">{{ old('dietary_notes') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Theme / Colour Palette / Style Notes</label>
                            <textarea name="theme_notes" rows="2"
                                      placeholder="e.g. Dusty rose and gold, rustic garden, no balloons…"
                                      class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] resize-none">{{ old('theme_notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-6 py-2 rounded-lg">
                        Book Appointment
                    </button>
                    <a href="{{ route('appointments.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
    function bookingForm(combos, allServices, oldServiceIds) {
        return {
            // ── Services dropdown ─────────────────────────────────────────────
            allServices,
            selectedServiceIds: oldServiceIds,
            svcOpen: false,
            svcSearch: '',
            customerId: '{{ old("customer_id", "") }}',

            get filteredServices() {
                const q = this.svcSearch.trim().toLowerCase();
                return q ? this.allServices.filter(s => s.name.toLowerCase().includes(q)) : this.allServices;
            },
            get groupedServiceList() {
                const groups = {};
                const order  = [];
                this.filteredServices.forEach(s => {
                    const key  = s.service_category_id ?? '__none__';
                    const name = s.category ? ((s.category.icon ?? '') + ' ' + s.category.name) : 'Uncategorised';
                    if (!groups[key]) { groups[key] = { label: name.trim(), services: [] }; order.push(key); }
                    groups[key].services.push(s);
                });
                const result = [];
                order.forEach(key => {
                    result.push({ _header: true, label: groups[key].label });
                    groups[key].services.forEach(s => result.push({ _header: false, ...s }));
                });
                return result;
            },
            get selectedServices() {
                return this.allServices.filter(s => this.selectedServiceIds.includes(s.id));
            },
            get summaryDuration() {
                const mins = this.selectedServices.reduce((n, s) => n + (s.duration_minutes || 0), 0);
                const h = Math.floor(mins / 60), m = mins % 60;
                return h > 0 ? (m > 0 ? `${h}h ${m}m` : `${h}h`) : `${m}m`;
            },
            get summaryPrice() {
                const total = this.selectedServices.reduce((n, s) => n + parseFloat(s.price || 0), 0);
                return total.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            isSelected(id) { return this.selectedServiceIds.includes(id); },

            toggleService(id) {
                this.isSelected(id)
                    ? (this.selectedServiceIds = this.selectedServiceIds.filter(i => i !== id))
                    : this.selectedServiceIds.push(id);
            },

            removeService(id) {
                this.selectedServiceIds = this.selectedServiceIds.filter(i => i !== id);
            },

            openSvc() {
                this.svcOpen = true;
                this.$nextTick(() => this.$refs.svcSearch?.focus());
            },

            fmtDur(mins) {
                const h = Math.floor(mins / 60), m = mins % 60;
                return h > 0 ? (m > 0 ? `${h}h ${m}m` : `${h}h`) : `${m}m`;
            },

            // ── Combos ───────────────────────────────────────────────────────
            combos,
            selectedComboId: '{{ old("combo_id", "") }}',

            getCombo() { return this.combos.find(c => c.id == this.selectedComboId) || null; },

            onComboChange() {
                const combo = this.getCombo();
                if (!combo) return;
                combo.service_ids.forEach(id => {
                    if (!this.selectedServiceIds.includes(id)) this.selectedServiceIds.push(id);
                });
            },

            // ── Client Intelligence ───────────────────────────────────────────
            intelBanner: null,
            intelDismissed: false,
            durationOverride: null,
            _intelTimer: null,

            checkClientIntel() {
                clearTimeout(this._intelTimer);
                if (!this.customerId || this.selectedServiceIds.length === 0) {
                    this.intelBanner = null;
                    return;
                }
                this._intelTimer = setTimeout(() => {
                    const params = new URLSearchParams();
                    params.set('customer_id', this.customerId);
                    this.selectedServiceIds.forEach(id => params.append('service_ids[]', id));
                    fetch(`{{ route('appointments.client-history') }}?` + params.toString(), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.sufficient_data) {
                            this.intelBanner   = data;
                            this.intelDismissed = false;
                        } else {
                            this.intelBanner = null;
                        }
                    })
                    .catch(() => { this.intelBanner = null; });
                }, 600);
            },

            acceptSuggestion() {
                if (this.intelBanner) {
                    this.durationOverride = this.intelBanner.avg_actual;
                }
            },

            dismissIntel() {
                this.intelDismissed   = true;
                this.durationOverride = null;
            },

            // ── Init ──────────────────────────────────────────────────────────
            init() {
                if (this.selectedComboId) this.$nextTick(() => this.onComboChange(false));
                this.$watch('customerId', () => { this.intelDismissed = false; this.checkClientIntel(); });
                this.$watch('selectedServiceIds', () => { if (!this.intelDismissed) this.checkClientIntel(); });
            },
        };
    }
    </script>
</x-app-layout>
