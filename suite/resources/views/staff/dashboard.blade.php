<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h1 class="text-lg font-semibold">Staff Dashboard</h1>
            <hr class="flex-1 mx-4 border-slate-600">
        </div>
    </x-slot>

    <div class="space-y-4">
    <div class="bg-slate-900 border border-slate-800 rounded-lg p-4">
        <div class="overflow-x-auto">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="text-slate-400 text-left">
                        <th class="px-3 py-2">Staff</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Now</th>
                        <th class="px-3 py-2">Next</th>
                        <th class="px-3 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody id="staff-list">
                @foreach($staffList as $s)
                    <tr id="staff-row-{{ $s['id'] }}" class="border-t border-slate-800">
                        <td class="px-3 py-3">{{ $s['name'] }}<div class="text-xs text-slate-500">{{ $s['email'] }} · {{ $s['phone'] }}</div></td>
                        <td class="px-3 py-3"><span class="status text-xs font-bold text-amber-400">{{ $s['status'] }}</span></td>
                        <td class="px-3 py-3">
                            @if($s['current_appointment'])
                                <div class="text-sm">{{ $s['current_appointment']->services->pluck('name')->join(', ') ?: 'Appointment' }}</div>
                                <div class="text-xs text-slate-500">{{ $s['current_appointment']->scheduled_at->format('H:i') }}</div>
                            @else
                                <div class="text-sm text-slate-500">—</div>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            @if($s['next_appointment'])
                                <div class="text-sm">{{ $s['next_appointment']->services->pluck('name')->join(', ') ?: 'Appointment' }}</div>
                                <div class="text-xs text-slate-500">{{ $s['next_appointment']->scheduled_at->format('Y-m-d H:i') }}</div>
                            @else
                                <div class="text-sm text-slate-500">—</div>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            <a href="{{ route('staff.show', $s['id']) }}" class="text-sky-400 hover:underline text-sm">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
    <script>
        (function(){
            if (typeof Echo === 'undefined') return;

            // Listen for staff status updates (requires server-side broadcast events)
            Echo.channel('staff-status')
                .listen('StaffStatusUpdated', (e) => {
                    // Expected payload: { id, status, current_appointment, next_appointment }
                    const row = document.querySelector(`#staff-row-${e.id}`);
                    if (row) {
                        const statusEl = row.querySelector('.status');
                        if (statusEl) statusEl.textContent = e.status;
                        // Could update appointment times here if payload includes them
                    }
                });
        })();
    </script>
</x-app-layout>
