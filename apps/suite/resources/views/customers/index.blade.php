<x-app-layout>
    <x-slot name="header">Customers</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Name, email or phone…"
                       class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 w-56 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <select name="status" class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Filter</button>
                @if(request()->hasAny(['search','status']))
                    <a href="{{ route('customers.index') }}" class="text-sm px-4 py-2 rounded-lg text-slate-400 hover:text-white">Clear</a>
                @endif
            </form>
            <a href="{{ route('customers.create') }}"
               class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                + New Customer
            </a>
        </div>

        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Phone</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3">
                                <a href="{{ route('customers.show', $customer) }}" class="text-indigo-400 hover:text-indigo-300 font-medium">
                                    {{ $customer->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $customer->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-300">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($customer->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('customers.edit', $customer) }}" class="text-slate-400 hover:text-white text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $customers->links() }}
    </div>
</x-app-layout>
