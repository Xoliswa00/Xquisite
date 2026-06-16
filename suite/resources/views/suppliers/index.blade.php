<x-app-layout>
    <x-slot name="header">Suppliers</x-slot>

    <div class="max-w-4xl space-y-4">

        <div class="flex items-center justify-between">
            <div></div>
            <a href="{{ route('suppliers.create') }}"
               class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-5 py-2 rounded-lg">
                Add Supplier
            </a>
        </div>

        <div class="bg-slate-800 rounded-xl overflow-hidden">
            <table class="w-full text-sm summary-on-mobile">
                <thead>
                    <tr class="border-b border-slate-700 text-slate-400 text-left">
                        <th class="px-4 py-3 font-medium">Supplier</th>
                        <th class="px-4 py-3 font-medium">Contact</th>
                        <th class="px-4 py-3 font-medium">Payment Terms</th>
                        <th class="px-4 py-3 font-medium text-center">Products</th>
                        <th class="px-4 py-3 font-medium text-center">Orders</th>
                        <th class="px-4 py-3 font-medium text-center">Status</th>
                        <th class="px-4 py-3 font-medium text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-slate-700/30">
                            <td class="px-4 py-3">
                                <p class="font-medium text-white">{{ $supplier->name }}</p>
                                @if($supplier->email)
                                    <p class="text-xs text-slate-500">{{ $supplier->email }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400">
                                <p>{{ $supplier->contact_person ?: '—' }}</p>
                                @if($supplier->phone)
                                    <p class="text-xs text-slate-500">{{ $supplier->phone }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ $supplier->payment_terms ?: '—' }}</td>
                            <td class="px-4 py-3 text-center text-slate-400">{{ $supplier->products_count }}</td>
                            <td class="px-4 py-3 text-center text-slate-400">{{ $supplier->purchase_orders_count }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($supplier->is_active)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-emerald-900/50 text-emerald-400 border border-emerald-800">Active</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-slate-700 text-slate-400 border border-slate-600">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('suppliers.show', $supplier) }}"
                                   class="text-xs text-indigo-400 hover:text-indigo-300">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                No suppliers yet.
                                <a href="{{ route('suppliers.create') }}" class="text-indigo-400 hover:text-indigo-300 ml-1">Add one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-app-layout>
