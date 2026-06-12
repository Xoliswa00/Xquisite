<x-app-layout>
    <x-slot name="header">{{ $customer->name }}</x-slot>

    <div class="max-w-3xl space-y-4">

        <!-- Profile card -->
        <div class="bg-slate-800 rounded-xl p-6 flex items-start justify-between">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-white">{{ $customer->name }}</h2>
                @if($customer->email)
                    <p class="text-sm text-slate-400">{{ $customer->email }}</p>
                @endif
                @if($customer->phone)
                    <p class="text-sm text-slate-400">{{ $customer->phone }}</p>
                @endif
                @if($customer->notes)
                    <p class="text-sm text-slate-500 pt-1">{{ $customer->notes }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @if($customer->is_active)
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                @else
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                @endif
                <a href="{{ route('customers.edit', $customer) }}"
                   class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Edit</a>
                <a href="{{ route('appointments.create') }}?customer_id={{ $customer->id }}"
                   class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg">+ Book</a>
            </div>
        </div>

        <!-- Appointment history -->
        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-700">
                <h3 class="text-sm font-medium text-slate-300">Appointment History</h3>
            </div>
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Date</th>
                        <th class="px-4 py-3 font-medium">Service</th>
                        <th class="px-4 py-3 font-medium">Staff</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($appointments as $appt)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3 text-slate-300">{{ $appt->scheduled_at->format('d M Y, H:i') }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $appt->service->name }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $appt->staff->name }}</td>
                            <td class="px-4 py-3">
                                @php $colors = ['pending'=>'yellow','confirmed'=>'emerald','completed'=>'blue','cancelled'=>'red','no_show'=>'slate']; $c = $colors[$appt->status] ?? 'slate'; @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-900/50 text-{{ $c }}-400 border border-{{ $c }}-800">
                                    {{ ucfirst(str_replace('_', ' ', $appt->status)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('appointments.show', $appt) }}" class="text-slate-400 hover:text-white text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">No appointments yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if($appointments->hasPages())
                <div class="px-4 py-3 border-t border-slate-700">{{ $appointments->links() }}</div>
            @endif
        </div>

        <a href="{{ route('customers.index') }}" class="inline-block text-sm text-slate-400 hover:text-white">← Back to customers</a>
    </div>
</x-app-layout>
