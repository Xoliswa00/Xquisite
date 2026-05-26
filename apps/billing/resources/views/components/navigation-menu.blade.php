<nav x-data="{ open: false }" class="bg-white/80 backdrop-blur-xl sticky top-0 z-[110] border-b border-slate-200/60">

    @php
        $user = Auth::user();
        $company = optional($user->currentCompany);
        $companyName = $company->name ?? 'Workspace';

        $navItems = $user->isClientUser()
            ? [
                ['route' => 'dashboard',      'label' => 'Overview'],
                ['route' => 'clients.profile','label' => 'My Profile'],
            ]
            : [
                ['route' => 'dashboard',       'label' => 'Overview'],
                ['route' => 'clients.index',   'label' => 'Clients'],
                ['route' => 'quotes.index',    'label' => 'Quotes'],
                ['route' => 'invoices.index',  'label' => 'Invoices'],
                ['route' => 'payments.index',  'label' => 'Payments'],
                ['route' => 'products.index',  'label' => 'Products'],
                ['route' => 'reports.index',   'label' => 'Reports'],
            ];
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <div class="flex items-center">
                <a href="{{ route('dashboard') }}" class="flex items-center group">
                    <div class="w-8 h-8 bg-slate-900 rounded-lg flex items-center justify-center mr-3 group-hover:bg-indigo-600 transition">
                        <span class="text-white text-xs font-black">X</span>
                    </div>
                    <span class="font-bold text-slate-900 tracking-tight hidden sm:block">
                        {{ $companyName }}
                    </span>
                </a>

                <div class="hidden sm:flex sm:ml-8 space-x-1">
                    @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition
                           {{ request()->routeIs($item['route']) ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="hidden sm:flex items-center space-x-3">
                <a href="{{ route('invoices.create') }}"
                   class="inline-flex items-center px-3 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-indigo-600 transition">
                    + Invoice
                </a>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex rounded-full overflow-hidden ring-1 ring-slate-200 hover:ring-indigo-400 transition">
                            <img class="h-8 w-8 object-cover" src="{{ $user->profile_photo_url }}">
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-3 border-b">
                            <p class="text-sm font-bold text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                        </div>
                        <x-dropdown-link href="{{ route('profile.show') }}">Account Settings</x-dropdown-link>
                        <x-dropdown-link href="{{ route('companies.index') }}">Company Settings</x-dropdown-link>
                        <div class="border-t"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link href="{{ route('logout') }}"
                                onclick="event.preventDefault(); this.closest('form').submit();"
                                class="text-red-600">
                                Sign Out
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="p-2 rounded-lg bg-slate-50">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2">
                        <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="open" stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div x-show="open" x-transition @click.outside="open = false"
         class="sm:hidden border-t bg-white px-4 pb-4 pt-2 space-y-1">
        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium
               {{ request()->routeIs($item['route']) ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                {{ $item['label'] }}
            </a>
        @endforeach
        <div class="border-t pt-3 mt-2 space-y-1">
            <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-sm text-slate-600">Account</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full text-left px-3 py-2 text-sm text-red-600">Sign Out</button>
            </form>
        </div>
    </div>
</nav>
