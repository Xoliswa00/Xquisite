<x-app-layout>
    <x-slot name="header">Bookings</x-slot>

    <div class="space-y-4">

        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form id="appointments-filter-form" method="GET" class="flex flex-wrap gap-2">
                <input id="appointment-search" type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search customer…"
                       class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 w-48 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <input id="appointment-date" type="date" name="date" value="{{ request('date') }}"
                       class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <select id="appointment-status" name="status" class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    @foreach(['pending','confirmed','completed','cancelled','no_show'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                    <option value="unassigned" @selected(request('status') === 'unassigned')>Unassigned Staff</option>
                </select>
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Filter</button>
                @if(request()->hasAny(['search','date','status']))
                    <a href="{{ route('appointments.index') }}" class="text-sm px-4 py-2 rounded-lg text-slate-400 hover:text-white">Clear</a>
                @endif
            </form>
            <a href="{{ route('appointments.create') }}"
               class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                + New Booking
            </a>
        </div>

        <!-- Table -->
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Date & Time</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Service</th>
                        <th class="px-4 py-3 font-medium">Staff</th>
                        <th class="px-4 py-3 font-medium">Duration</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($appointments as $appt)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3 text-slate-300">
                                {{ $appt->scheduled_at->format('d M Y') }}<br>
                                <span class="text-xs text-slate-500">{{ $appt->scheduled_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('customers.show', $appt->customer) }}" class="text-indigo-400 hover:text-indigo-300">
                                    {{ $appt->customer->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $appt->service->name }}</td>
                            <td class="px-4 py-3">
                                @if($appt->staff_id)
                                    <span class="text-slate-300">{{ $appt->staff->name }}</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-900/40 text-orange-400 border border-orange-800">
                                        Unassigned
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ $appt->duration_minutes }}m</td>
                            <td class="px-4 py-3">
                                @php $colors = ['pending'=>'yellow','confirmed'=>'emerald','completed'=>'blue','cancelled'=>'red','no_show'=>'slate']; $c = $colors[$appt->status] ?? 'slate'; @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800">
                                    {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('appointments.show', $appt) }}" class="text-slate-400 hover:text-white text-xs mr-3">View</a>
                                <a href="{{ route('appointments.edit', $appt) }}" class="text-slate-400 hover:text-white text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-slate-500">No appointments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $appointments->links() }}
    </div>

    <script>
        (() => {
            const form = document.getElementById('appointments-filter-form');
            const search = document.getElementById('appointment-search');
            const date = document.getElementById('appointment-date');
            const status = document.getElementById('appointment-status');
            let debounceTimeout;

            if (!form) {
                return;
            }

            const submitForm = () => form.submit();

            if (date) {
                date.addEventListener('change', submitForm);
            }

            if (status) {
                status.addEventListener('change', submitForm);
            }

            if (search) {
                const submitSearch = () => {
                    clearTimeout(debounceTimeout);
                    debounceTimeout = setTimeout(submitForm, 450);
                };

                search.addEventListener('input', submitSearch);
                search.addEventListener('change', submitSearch);
            }
        })();
    </script>
</x-app-layout>
