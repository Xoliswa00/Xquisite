<a href="{{ route('stock.take') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posStockTakeRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
    Stock Take
</a>
<a href="{{ route('stock.reorder-alerts') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posStockReorderRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <span class="flex-1">Reorder Alerts</span>
    @if($sidebarReorderCount > 0)
        <span class="bg-amber-500 text-slate-950 text-xs font-bold px-1.5 py-0.5 rounded-full leading-none">
            {{ $sidebarReorderCount }}
        </span>
    @endif
</a>
<a href="{{ route('purchase-orders.index') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posPurchaseOrderRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Purchase Orders
</a>
