<x-app-layout>
    <x-slot name="header">New Booking</x-slot>

    <div class="max-w-2xl">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('appointments.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Customer</label>
                    <select name="customer_id" required
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('customer_id') border-red-500 @enderror">
                        <option value="">Select customer…</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Services multi-select --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Services</label>
                        <select name="service_ids[]" id="service_ids" multiple required
                                class="w-full @error('service_ids') border-red-500 @enderror">
                            @foreach($services as $s)
                                <option value="{{ $s->id }}"
                                        data-duration="{{ $s->duration_minutes }}"
                                        data-price="{{ $s->price }}"
                                        @selected(collect(old('service_ids', []))->contains($s->id))>
                                    {{ $s->name }} ({{ $s->duration_minutes }}m — R{{ number_format($s->price, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('service_ids')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                        @error('service_ids.*')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">
                            Staff
                            <span class="text-slate-500 font-normal text-xs ml-1">(optional — assign later)</span>
                        </label>
                        <select name="staff_id"
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('staff_id') border-red-500 @enderror">
                            <option value="">— Unassigned —</option>
                            @foreach($staff as $m)
                                <option value="{{ $m->id }}" @selected(old('staff_id') == $m->id)>{{ $m->name }}</option>
                            @endforeach
                        </select>
                        @error('staff_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Duration summary — read-only, computed from selected services --}}
                <div id="duration-summary" class="hidden rounded-lg bg-slate-700/50 border border-slate-600 px-4 py-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-400">Total duration</span>
                        <span id="duration-total" class="text-slate-100 font-medium"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm mt-1">
                        <span class="text-slate-400">Estimated total</span>
                        <span id="price-total" class="text-indigo-400 font-medium"></span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" required
                           value="{{ old('scheduled_at') }}"
                           class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('scheduled_at') border-red-500 @enderror">
                    @error('scheduled_at')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Status</label>
                    <select name="status" class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @foreach(['pending','confirmed','completed','cancelled','no_show','tentative'] as $s)
                            <option value="{{ $s }}" @selected(old('status', 'pending') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                              placeholder="Optional notes…">{{ old('notes') }}</textarea>
                </div>

                {{-- Event Brief --}}
                <div x-data="{ open: {{ old('headcount') || old('venue') || old('event_type') ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                            class="flex items-center gap-2 text-sm text-indigo-400 hover:text-indigo-300 transition">
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
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Event Type</label>
                                <select name="event_type"
                                        class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
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
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Setup Time</label>
                                <input type="datetime-local" name="setup_at" value="{{ old('setup_at') }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Breakdown / Collection Time</label>
                                <input type="datetime-local" name="breakdown_at" value="{{ old('breakdown_at') }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Dietary Requirements</label>
                            <textarea name="dietary_notes" rows="2"
                                      placeholder="e.g. 10 vegetarian, 5 halaal, 2 nut allergy…"
                                      class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-none">{{ old('dietary_notes') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Theme / Colour Palette / Style Notes</label>
                            <textarea name="theme_notes" rows="2"
                                      placeholder="e.g. Dusty rose and gold, rustic garden, no balloons…"
                                      class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-none">{{ old('theme_notes') }}</textarea>
                        </div>

                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded-lg">
                        Book Appointment
                    </button>
                    <a href="{{ route('appointments.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tom Select --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <style>
        /* Match Tom Select to your slate dark theme */
        .ts-wrapper.multi .ts-control {
            background-color: rgb(51 65 85);   /* slate-700 */
            border-color:     rgb(71 85 105);  /* slate-600 */
            border-radius:    0.5rem;
            padding:          0.375rem 0.625rem;
            box-shadow:       none;
        }
        .ts-wrapper.multi .ts-control input {
            color: rgb(241 245 249); /* slate-100 */
            font-size: 0.875rem;
        }
        .ts-wrapper.multi .ts-control input::placeholder { color: rgb(100 116 139); }
        .ts-dropdown {
            background-color: rgb(30 41 59);  /* slate-800 */
            border-color:     rgb(71 85 105);
            border-radius:    0.5rem;
            font-size:        0.875rem;
        }
        .ts-dropdown .option         { color: rgb(203 213 225); padding: 0.5rem 0.75rem; }
        .ts-dropdown .option:hover,
        .ts-dropdown .option.active  { background-color: rgb(67 56 202); color: #fff; } /* indigo-700 */
        .ts-dropdown .option.selected { background-color: rgb(79 70 229); color: #fff; } /* indigo-600 */
        .ts-wrapper .item {
            background-color: rgb(67 56 202) !important; /* indigo-700 */
            color: #fff !important;
            border-radius: 0.375rem !important;
            border: none !important;
            font-size: 0.75rem;
            padding: 2px 8px !important;
        }
        .ts-wrapper .item .remove { color: rgb(165 180 252); border-left-color: rgba(255,255,255,0.2); }
        .ts-wrapper .item .remove:hover { color: #fff; background-color: rgb(79 70 229); }
        .ts-wrapper.focus .ts-control { border-color: rgb(99 102 241); box-shadow: 0 0 0 1px rgb(99 102 241); }
    </style>

    <script>
        const tomSelect = new TomSelect('#service_ids', {
            plugins: ['remove_button'],
            placeholder: 'Select services…',
            onChange() { updateSummary(); },
        });

        function updateSummary() {
            const selected = tomSelect.getValue(); // array of selected IDs (strings)
            if (!selected.length) {
                document.getElementById('duration-summary').classList.add('hidden');
                return;
            }

            let totalMins  = 0;
            let totalPrice = 0;

            selected.forEach(id => {
                const opt = document.querySelector(`#service_ids option[value="${id}"]`);
                if (opt) {
                    totalMins  += parseInt(opt.dataset.duration, 10) || 0;
                    totalPrice += parseFloat(opt.dataset.price)      || 0;
                }
            });

            const h = Math.floor(totalMins / 60);
            const m = totalMins % 60;
            const durationLabel = h > 0
                ? (m > 0 ? `${h}h ${m}m` : `${h}h`)
                : `${m}m`;

            document.getElementById('duration-total').textContent = durationLabel;
            document.getElementById('price-total').textContent    = 'R' + totalPrice.toLocaleString('en-ZA', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('duration-summary').classList.remove('hidden');
        }

        // Re-run on page load if old() re-selected values after a validation error
        updateSummary();
    </script>
</x-app-layout>
