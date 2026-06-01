<x-app-layout>
    <x-slot name="header">Edit Booking</x-slot>

    <div class="max-w-2xl">
        <div class="bg-slate-800 rounded-xl p-6">
            <form method="POST" action="{{ route('appointments.update', $appointment) }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Customer</label>
                    <select name="customer_id" required
                            class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id', $appointment->customer_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Service</label>
                        <select name="service_id" required
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            @foreach($services as $s)
                                <option value="{{ $s->id }}" @selected(old('service_id', $appointment->service_id) == $s->id)>
                                    {{ $s->name }} ({{ $s->duration_minutes }}m)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">
                            Staff
                            <span class="text-slate-500 font-normal text-xs ml-1">(cleared automatically if you change the date/time)</span>
                        </label>
                        <select name="staff_id"
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option value="">— Unassigned —</option>
                            @foreach($staff as $m)
                                <option value="{{ $m->id }}" @selected(old('staff_id', $appointment->staff_id) == $m->id)>{{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" required
                               value="{{ old('scheduled_at', $appointment->scheduled_at->format('Y-m-d\TH:i')) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" min="5" max="480" required
                               value="{{ old('duration_minutes', $appointment->duration_minutes) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Status</label>
                    <select name="status" class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @foreach(['pending','confirmed','completed','cancelled','no_show'] as $s)
                            <option value="{{ $s }}" @selected(old('status', $appointment->status) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">{{ old('notes', $appointment->notes) }}</textarea>
                </div>

                {{-- Event Brief --}}
                <div x-data="{ open: {{ $appointment->isEventBooking() ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                            class="flex items-center gap-2 text-sm text-indigo-400 hover:text-indigo-300 transition">
                        <svg class="w-4 h-4 transition" :class="open ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                        Event Brief
                        <span class="text-slate-500 font-normal">(catering, decor & events)</span>
                    </button>

                    <div x-show="open" x-transition class="mt-4 space-y-4 border-t border-slate-700 pt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Guest Count</label>
                                <input type="number" name="headcount" min="1"
                                       value="{{ old('headcount', $appointment->headcount) }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Event Type</label>
                                <select name="event_type"
                                        class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
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
                                   class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Setup Time</label>
                                <input type="datetime-local" name="setup_at"
                                       value="{{ old('setup_at', $appointment->setup_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-300 mb-1">Breakdown Time</label>
                                <input type="datetime-local" name="breakdown_at"
                                       value="{{ old('breakdown_at', $appointment->breakdown_at?->format('Y-m-d\TH:i')) }}"
                                       class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Dietary Requirements</label>
                            <textarea name="dietary_notes" rows="2"
                                      class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-none">{{ old('dietary_notes', $appointment->dietary_notes) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1">Theme / Style Notes</label>
                            <textarea name="theme_notes" rows="2"
                                      class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 resize-none">{{ old('theme_notes', $appointment->theme_notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded-lg">
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
</x-app-layout>
