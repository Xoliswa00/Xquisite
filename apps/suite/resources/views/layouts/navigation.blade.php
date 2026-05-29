@php
    use App\Models\Modules\Core\Models\SystemLog;
    $criticalCount = 0;
    try { $criticalCount = SystemLog::unresolvedCriticalCount(); } catch (\Throwable) {}
@endphp

<nav class="bg-slate-900 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-wide text-white">
                    Xquisite
                </a>
                <div class="hidden md:flex gap-4 text-sm text-slate-300">
                    <a href="{{ route('dashboard') }}" class="hover:text-white {{ request()->routeIs('dashboard') ? 'text-white' : '' }}">Dashboard</a>
                    <a href="{{ route('appointments.index') }}" class="hover:text-white {{ request()->routeIs('appointments.*') ? 'text-white' : '' }}">Bookings</a>
                    <a href="{{ route('customers.index') }}" class="hover:text-white {{ request()->routeIs('customers.*') ? 'text-white' : '' }}">Customers</a>
                    <a href="{{ route('staff.index') }}" class="hover:text-white {{ request()->routeIs('staff.*') ? 'text-white' : '' }}">Staff</a>
                    <a href="{{ route('services.index') }}" class="hover:text-white {{ request()->routeIs('services.*') ? 'text-white' : '' }}">Services</a>
                </div>
            </div>

            <div class="flex items-center gap-4">

                {{-- Error badge --}}
                @if($criticalCount > 0)
                    <a href="{{ route('admin.logs.index', ['status' => 'new', 'level' => 'ERROR']) }}"
                       class="relative flex items-center gap-1.5 px-3 py-1.5 bg-red-700/80 hover:bg-red-600 rounded-lg text-xs font-semibold text-white transition">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $criticalCount }} error{{ $criticalCount > 1 ? 's' : '' }}
                    </a>
                @endif

                <div class="text-sm text-slate-300 hidden sm:block">{{ Auth::user()->name }}</div>

                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="bg-slate-800 px-3 py-2 rounded-lg text-sm hover:bg-slate-700">Menu</button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute right-0 mt-2 w-56 bg-slate-800 rounded-lg shadow-lg overflow-hidden">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-700 text-slate-300">Profile</a>
                        <a href="{{ route('settings.modules.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-700 text-slate-300">Modules</a>
                        <div class="border-t border-slate-700 my-1"></div>
                        <a href="{{ route('admin.tenants.index') }}" class="block px-4 py-2 text-sm hover:bg-slate-700 text-slate-300">
                            Admin — Tenants
                        </a>
                        <a href="{{ route('admin.logs.index') }}" class="flex items-center justify-between px-4 py-2 text-sm hover:bg-slate-700 text-slate-300">
                            <span>System Logs</span>
                            @if($criticalCount > 0)
                                <span class="bg-red-600 text-white text-xs rounded-full px-1.5 py-0.5 font-bold">{{ $criticalCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.logs.audit') }}" class="block px-4 py-2 text-sm hover:bg-slate-700 text-slate-300">Audit Trail</a>
                        <a href="{{ route('admin.logs.combined') }}" class="block px-4 py-2 text-sm hover:bg-slate-700 text-slate-300">Combined Logs</a>
                        <div class="border-t border-slate-700 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-700 text-slate-400">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</nav>
