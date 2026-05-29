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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Service</label>
                        <select name="service_id" id="service_id" required
                                class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('service_id') border-red-500 @enderror">
                            <option value="">Select service…</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}" data-duration="{{ $s->duration_minutes }}" @selected(old('service_id') == $s->id)>
                                    {{ $s->name }} ({{ $s->duration_minutes }}m — R{{ number_format($s->price, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('service_id')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" required
                               value="{{ old('scheduled_at') }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('scheduled_at') border-red-500 @enderror">
                        @error('scheduled_at')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-1">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" id="duration_minutes" min="5" max="480" required
                               value="{{ old('duration_minutes', 60) }}"
                               class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('duration_minutes') border-red-500 @enderror">
                        @error('duration_minutes')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Status</label>
                    <select name="status" class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @foreach(['pending','confirmed','completed','cancelled','no_show'] as $s)
                            <option value="{{ $s }}" @selected(old('status', 'pending') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                              class="w-full bg-slate-700 border border-slate-600 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                              placeholder="Optional notes…">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-6 py-2 rounded-lg">
                        Book Appointment
                    </button>
                    <a href="{{ route('appointments.index') }}" class="text-sm text-slate-400 hover:text-white">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('service_id').addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const dur = opt.dataset.duration;
            if (dur) document.getElementById('duration_minutes').value = dur;
        });
    </script>
</x-app-layout>
