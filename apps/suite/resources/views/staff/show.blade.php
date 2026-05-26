<x-app-layout>
    <x-slot name="header">{{ $staff->name }}</x-slot>

    <div class="max-w-3xl space-y-4">

        <!-- Profile card -->
        <div class="bg-slate-800 rounded-xl p-6 flex items-start justify-between">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-white">{{ $staff->name }}</h2>
                <p class="text-sm text-slate-400">{{ $staff->role ?? 'Staff member' }}</p>
                @if($staff->email)
                    <p class="text-sm text-slate-400">{{ $staff->email }}</p>
                @endif
                @if($staff->phone)
                    <p class="text-sm text-slate-400">{{ $staff->phone }}</p>
                @endif
                @if($staff->services->count())
                    <div class="flex flex-wrap gap-1.5 pt-2">
                        @foreach($staff->services as $service)
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-slate-700 text-slate-300 border border-slate-600">{{ $service->name }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if($staff->is_active)
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                @else
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                @endif
                <a href="{{ route('staff.edit', $staff) }}"
                   class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Edit</a>
            </div>
        </div>

        <!-- Recent appointments -->
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700">
                <h3 class="text-sm font-medium text-slate-300">Recent Appointments</h3>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Customer</th>
                        <th class="px-4 py-3 font-medium">Service</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($recentAppointments as $appt)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3 text-slate-300">{{ $appt->scheduled_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('customers.show', $appt->customer) }}" class="text-indigo-400 hover:text-indigo-300">
                                    {{ $appt->customer->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $appt->service->name }}</td>
                            <td class="px-4 py-3">
                                @php $colors = ['pending'=>'yellow','confirmed'=>'emerald','completed'=>'blue','cancelled'=>'red','no_show'=>'slate']; $c = $colors[$appt->status] ?? 'slate'; @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800">
                                    {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">No appointments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('staff.index') }}" class="inline-block text-sm text-slate-400 hover:text-white">← Back to staff</a>
    </div>
</x-app-layout>
