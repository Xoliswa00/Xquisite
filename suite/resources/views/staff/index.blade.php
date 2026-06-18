<x-app-layout>
    <x-slot name="header">Staff</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" class="flex gap-2 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name or email…"
                       class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 w-full sm:w-48 focus:outline-none focus:ring-1 focus:ring-[#0078D4]">
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Search</button>
                @if(request('search'))
                    <a href="{{ route('staff.index') }}" class="text-sm px-4 py-2 rounded-lg text-slate-400 hover:text-white">Clear</a>
                @endif
            </form>
            <a href="{{ route('staff.create') }}"
               class="w-full sm:w-auto text-center bg-[#0078D4] hover:bg-[#0078D4] text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                + Add Staff
            </a>
        </div>

        <div class="bg-slate-800 rounded-xl overflow-hidden overflow-x-auto">

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-slate-700">
                @forelse($staff as $member)
                    <a href="{{ route('staff.show', $member) }}" class="block px-4 py-3 hover:bg-slate-700/50 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-[#0078D4]">{{ $member->name }}</p>
                            @if($member->is_active)
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                            @else
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $member->role ?? 'Staff member' }}</p>
                        @if($member->email || $member->phone)
                            <p class="text-xs text-slate-500 mt-0.5">{{ implode(' · ', array_filter([$member->email, $member->phone])) }}</p>
                        @endif
                        <p class="text-xs text-slate-500 mt-0.5">{{ $member->appointments_count }} bookings</p>
                    </a>
                @empty
                    <div class="px-4 py-10 text-center text-slate-500 text-sm">
                        No staff yet. <a href="{{ route('staff.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0]">Add a member.</a>
                    </div>
                @endforelse
            </div>

            {{-- Desktop table --}}
            <table class="hidden sm:table w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Role</th>
                        <th class="px-4 py-3 font-medium">Contact</th>
                        <th class="px-4 py-3 font-medium">Bookings</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($staff as $member)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('staff.show', $member) }}" class="text-[#0078D4] hover:text-[#B8D4F0] font-medium">
                                    {{ $member->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $member->role ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-400 text-xs">
                                {{ $member->email ?? '' }}<br>{{ $member->phone ?? '' }}
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $member->appointments_count }}</td>
                            <td class="px-4 py-3">
                                @if($member->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('staff.edit', $member) }}" class="text-slate-400 hover:text-white text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500">No staff yet. <a href="{{ route('staff.create') }}" class="text-[#0078D4] hover:text-[#B8D4F0]">Add a member.</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $staff->links() }}
    </div>
</x-app-layout>
