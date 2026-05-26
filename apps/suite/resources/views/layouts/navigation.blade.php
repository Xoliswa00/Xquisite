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
                <div class="text-sm text-slate-300 hidden sm:block">{{ Auth::user()->name }}</div>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="bg-slate-800 px-3 py-2 rounded-lg text-sm hover:bg-slate-700">Menu</button>
                    <div x-show="open" @click.away="open = false"
                         class="absolute right-0 mt-2 w-48 bg-slate-800 rounded-lg shadow-lg overflow-hidden">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-700">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-700">Log Out</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</nav>
