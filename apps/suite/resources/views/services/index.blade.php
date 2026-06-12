<x-app-layout>
    <x-slot name="header">Services</x-slot>

    <div class="space-y-4">
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search services…"
                       class="bg-slate-800 border border-slate-700 text-slate-100 text-sm rounded-lg px-3 py-2 w-48 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-sm px-4 py-2 rounded-lg">Search</button>
                @if(request('search'))
                    <a href="{{ route('services.index') }}" class="text-sm px-4 py-2 rounded-lg text-slate-400 hover:text-white">Clear</a>
                @endif
            </form>
            <a href="{{ route('services.create') }}"
               class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg whitespace-nowrap">
                + New Service
            </a>
        </div>

        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Duration</th>
                        <th class="px-4 py-3 font-medium">Price</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($services as $service)
                        <tr class="hover:bg-slate-700/50">
                            <td class="px-4 py-3">
                                <p class="text-white font-medium">{{ $service->name }}</p>
                                @if($service->description)
                                    <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($service->description, 60) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ $service->duration_minutes }} min</td>
                            <td class="px-4 py-3 text-slate-300">R{{ number_format($service->price, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($service->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('services.edit', $service) }}" class="text-slate-400 hover:text-white text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">No services yet. <a href="{{ route('services.create') }}" class="text-indigo-400 hover:text-indigo-300">Add one.</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $services->links() }}
    </div>
</x-app-layout>
