<x-app-layout>
    <x-slot name="header">Edit Booking</x-slot>

    <div class="max-w-2xl">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('appointments.update', $appointment) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                @if($errors->any())
                    <div class="bg-red-900/30 border border-red-700 rounded-xl px-5 py-4 text-sm text-red-300">
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Customer</label>
                    <select name="customer_id" required
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $appointment->customer_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <script>var initialStaffAvailability = {!! json_encode($staffAvailability, JSON_HEX_TAG) !!};</script>
                <div x-data="staffAvailabilityPanel(initialStaffAvailability, '{{ route('appointments.availability', $appointment) }}')">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Services</label>
                            <div class="flex flex-wrap gap-2 bg-slate-700/50 border border-slate-600 rounded-lg px-3 py-2.5 min-h-[42px]">
                                @foreach($appointment->services as $s)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-[#001A3A]/50 text-[#B8D4F0] border border-[#002B5B]">
                                        {{ $s->name }}
                                    </span>
                                    <input type="hidden" name="service_ids[]" value="{{ $s->id }}">
                                @endforeach
                            </div>
                            <p class="text-xs text-slate-500 mt-1">To change services, delete and re-book.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">
                                Staff
                                <span class="text-slate-500 font-normal text-xs ml-1">(cleared automatically if you change the date/time)</span>
                            </label>
                            <select name="staff_id"
                                    class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                <option value="">— Unassigned —</option>
                                @foreach($staff as $m)
                                    <option value="{{ $m->id }}" @selected(old('staff_id', $appointment->staff_id) == $m->id)>{{ $m->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Date & Time</label>
                            <input type="datetime-local" name="scheduled_at" required
                                   x-ref="scheduled_at"
                                   @input="update"
                                   @change="update"
                                   value="{{ old('scheduled_at', $appointment->scheduled_at->format('Y-m-d\TH:i')) }}"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Duration (minutes)</label>
                            <input type="number" name="duration_minutes" min="5" max="480" required
                                   x-ref="duration_minutes"
                                   @input="update"
                                   @change="update"
                                   value="{{ old('duration_minutes', $appointment->duration_minutes) }}"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-700 bg-slate-900 p-4">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <h3 class="text-sm font-semibold text-[#D4AF37]">Staff availability for this slot</h3>
                            <p class="text-xs text-slate-500">Based on the selected date, time, duration and current bookings.</p>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">
                            <span x-text="staffAvailability.filter(item => item.available).length"></span> available
                        </span>
                    </div>
                    <div class="grid gap-3">
                        <template x-for="info in staffAvailability" :key="info.id">
                            <div :class="info.available ? 'rounded-xl p-3 bg-slate-800 border border-slate-700' : 'rounded-xl p-3 bg-slate-950 border border-red-800'">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium text-white" x-text="info.name"></p>
                                        <p class="text-xs text-slate-400 mt-1" x-text="info.available ? 'Available for this slot' : info.reason"></p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-2 py-1 text-[11px] font-semibold"
                                          :class="info.available ? 'bg-emerald-500/15 text-emerald-300' : 'bg-red-500/10 text-red-300'"
                                          x-text="info.available ? 'Available' : 'Unavailable'"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Status</label>
                    <select name="status" class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        @foreach(['pending','confirmed','completed','cancelled','no_show'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $appointment->status) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">{{ old('notes', $appointment->notes) }}</textarea>
                </div>

                {{-- Event Brief --}}
                <div x-data="{ open: {{ $appointment->isEventBooking() ? 'true' : 'false' }} }">
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
                                <label class="block text-sm font-medium text-slate-300 mb-1">Guest Count</label>
                                <input type="number" name="headcount" min="1"
                                       value="{{ old('headcount', $appointment->headcount) }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Event Type</label>
                                <select name="event_type"
                                        class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                                    <option value="">— Select type —</option>
                                    @foreach(['Wedding','Corporate Function','Birthday','Private Party','Funeral','Product Launch','Year End Function','Other'] as $t)
                                        <option value="{{ $t }}" @selected(old('event_type', $appointment->event_type) === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Venue / Location</label>
                            <input type="text" name="venue"
                                   value="{{ old('venue', $appointment->venue) }}"
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Setup Time</label>
                                <input type="datetime-local" name="setup_at"
                                       value="{{ old('setup_at', $appointment->setup_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Breakdown Time</label>
                                <input type="datetime-local" name="breakdown_at"
                                       value="{{ old('breakdown_at', $appointment->breakdown_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Dietary Requirements</label>
                            <textarea name="dietary_notes" rows="2"
                                      class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] resize-none">{{ old('dietary_notes', $appointment->dietary_notes) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Theme / Style Notes</label>
                            <textarea name="theme_notes" rows="2"
                                      class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0078D4] resize-none">{{ old('theme_notes', $appointment->theme_notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-2">
                    <button type="submit" class="w-full sm:w-auto bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-6 py-2 rounded-lg">
                        Save Changes
                    </button>
                    <a href="{{ route('appointments.show', $appointment) }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>

        <!-- Danger zone -->
        <div class="mt-4 bg-slate-800 rounded-xl p-4 border border-red-900/50">
            <p class="text-sm text-slate-400 mb-3">Permanently delete this appointment.</p>
            <form method="POST" action="{{ route('appointments.destroy', $appointment) }}"
                  onsubmit="return confirm('Delete this appointment?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-700 hover:bg-red-600 text-white text-sm px-4 py-2 rounded-lg">
                    Delete Appointment
                </button>
            </form>
        </div>
    </div>

    <script>
        function staffAvailabilityPanel(initialAvailability, url) {
            return {
                staffAvailability: initialAvailability,
                url,
                init() {
                    this.update();
                },
                async update() {
                    const scheduled = this.$refs.scheduled_at?.value;
                    const duration = this.$refs.duration_minutes?.value;
                    if (!scheduled || !duration) {
                        return;
                    }

                    try {
                        const params = new URLSearchParams({
                            scheduled_at: scheduled,
                            duration_minutes: duration,
                        });
                        document.querySelectorAll('input[name="service_ids[]"]').forEach(input => {
                            params.append('service_ids[]', input.value);
                        });
                        const response = await fetch(`${this.url}?${params.toString()}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        });

                        if (!response.ok) {
                            return;
                        }

                        const data = await response.json();
                        this.staffAvailability = data.staffAvailability;
                    } catch (error) {
                        console.error('Failed to refresh staff availability:', error);
                    }
                },
            };
        }
    </script>
</x-app-layout>
