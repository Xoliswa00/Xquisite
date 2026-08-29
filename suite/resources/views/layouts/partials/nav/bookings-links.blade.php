<a href="{{ route('appointments.index') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($bookingRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    Appointments
</a>
<a href="{{ route('staff.dashboard') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($bookingRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4 6h16M4 18h16M4 12h16"/></svg>
    Staff Dashboard
</a>
<a href="{{ route('appointments.calendar') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($bookingRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    Calendar
</a>
<a href="{{ route('customers.index') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($bookingRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-3m-4 6H7a4 4 0 01-4-4v-2a4 4 0 014-4h1m4 0a4 4 0 100-8 4 4 0 000 8z"/></svg>
    Customers
</a>
<a href="{{ route('services.index') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($bookingRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 3v12M6 15l-3 3m3-3l3 3M18 3v12M18 15l-3 3m3-3l3 3"/></svg>
    Services
</a>
<a href="{{ route('staff.index') }}"
   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($bookingRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-3m-4 6H7a4 4 0 01-4-4v-2a4 4 0 014-4h1m4 0a4 4 0 100-8 4 4 0 000 8z"/></svg>
    Staff
</a>
@if($authTenant)
    <div class="px-3 mt-2">
        <p class="text-xs text-slate-500 mb-1">Shareable links</p>
        <div class="flex flex-col gap-2">
            <a href="{{ $bookingUrl }}" target="_blank" rel="noopener" class="text-slate-400 hover:text-white text-sm px-3 py-2 rounded-lg bg-slate-800/20 hover:bg-slate-800">Open booking</a>
            <button type="button" onclick="navigator.clipboard.writeText('{{ $bookingUrl }}')" class="px-3 py-2 rounded-lg bg-slate-700 text-slate-200 text-sm hover:bg-slate-600">Copy link</button>
        </div>
    </div>
@endif
