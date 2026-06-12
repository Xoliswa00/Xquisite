<x-app-layout>
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8 space-y-8">

    {{-- HEADER SECTION --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-8">
        <div>
            <nav class="flex mb-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-slate-400">
                    <li>Inventory</li>
                    <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"></path></svg></li>
                    <li class="text-slate-900">Products Catalog</li>
                </ol>
            </nav>
            <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Products</h2>
        </div>

        <a href="{{ route('products.create') }}"
           class="inline-flex items-center justify-center bg-slate-900 hover:bg-brand-gold hover:text-slate-900 text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all duration-300 shadow-lg shadow-slate-200 group">
            <svg class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            Create New Product
        </a>
    </div>

    {{-- STATS GRID: "Glass" Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach([
            ['label' => 'Total Products', 'value' => $stats['total_products'], 'color' => 'slate'],
            ['label' => 'Active Status', 'value' => $stats['active_products'], 'color' => 'emerald'],
            ['label' => 'Recurring Rev', 'value' => $stats['recurring_products'], 'color' => 'indigo'],
            ['label' => 'Monthly Delta', 'value' => $stats['new_this_month'], 'color' => 'amber']
        ] as $stat)
        <div class="bg-white border border-slate-200/60 p-6 rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.02)] relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-3 opacity-10 group-hover:scale-110 transition-transform">
                <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
            </div>
            <p class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400 mb-1">{{ $stat['label'] }}</p>
            <h3 class="text-3xl font-bold text-slate-900 tabular-nums">{{ $stat['value'] }}</h3>
        </div>
        @endforeach
    </div>

    {{-- TABLE SECTION --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 border-b border-slate-200">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Product Identity</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Classification</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500">Billing Model</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Unit Price</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-center">System State</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-slate-500 text-right">Management</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/80 transition-colors group">
                        
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-slate-400 mr-3 font-bold text-xs uppercase">
                                    {{ substr($product->name, 0, 2) }}
                                </div>
                                <span class="text-sm font-bold text-slate-900 group-hover:text-brand-gold transition-colors">{{ $product->name }}</span>
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="text-[11px] font-bold uppercase tracking-tighter px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                {{ $product->type }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-500">
                            <div class="flex flex-col">
                                <span class="font-medium text-slate-700 capitalize">{{ $product->billing_type }}</span>
                                @if($product->billing_cycle)
                                    <span class="text-[10px] uppercase font-bold text-slate-400">{{ $product->billing_cycle }}</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 text-right tabular-nums">
                            <span class="text-sm font-black text-slate-900">
                                {{ $product->pricing->first() ? 'R' . number_format($product->pricing->first()->price, 2) : '—' }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-center">
                                @if($product->is_active)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600 border border-emerald-100">
                                        <span class="w-1 h-1 rounded-full bg-emerald-500 mr-1 animate-pulse"></span> Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 text-slate-400 border border-slate-200">
                                        Offline
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-end items-center gap-4">
                                <a href="{{ route('products.edit', $product) }}" class="text-slate-400 hover:text-slate-900 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>

                                <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button onclick="return confirm('Archive record?')" class="text-slate-300 hover:text-red-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-20 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">No Database Records Found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-6 border-t border-slate-100 pt-6">
        {{ $products->links() }}
    </div>
</div>
</x-app-layout>