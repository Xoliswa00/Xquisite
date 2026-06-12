<nav x-data="{ open: false }" class="bg-white/80 backdrop-blur-xl sticky top-0 z-[110] border-b border-slate-200/60">

    @php
        $user = Auth::user();
        $company = optional($user->currentCompany);
        $companyName = $company->name ?? 'Workspace';

        // Role-based navigation
        $navItems = $user->isClientUser()
            ? [
                ['route' => 'portal.dashboard', 'label' => 'Dashboard'],
                ['route' => 'portal.messages',  'label' => 'Messages'],
                ['route' => 'notifications.index', 'label' => 'Notifications'],
                ['route' => 'clients.profile',  'label' => 'My Profile'],
            ]
            : ($user->isSystemAdmin()
                ? [
                    ['route' => 'dashboard',           'label' => 'Overview'],
                    ['route' => 'admin.billing.index',  'label' => 'Platform Billing'],
                    ['route' => 'clients.index',        'label' => 'Clients'],
                ]
                : [
                    ['route' => 'dashboard',      'label' => 'Overview'],
                    ['route' => 'products.index',  'label' => 'Products'],
                    ['route' => 'clients.index',   'label' => 'Clients'],
                    ['route' => 'quotes.index',    'label' => 'Quotes'],
                    ['route' => 'invoices.index',  'label' => 'Invoices'],
                    ['route' => 'billing.index',   'label' => 'Billing'],
                ]);
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- LEFT: Logo + Nav -->
            <div class="flex items-center">

                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="flex items-center group">
                    <div class="w-8 h-8 bg-slate-900 rounded-lg flex items-center justify-center mr-3 group-hover:bg-brand-gold transition">
                        <span class="text-white text-xs font-black">X</span>
                    </div>
                    <span class="font-bold text-slate-900 tracking-tight">
                        {{ $companyName }}
                    </span>

                    <!-- Role Badge -->
                    @if($user->isClientUser())
                        <span class="ml-2 text-[10px] px-2 py-0.5 bg-blue-100 text-blue-600 rounded-full">
                            Client
                        </span>
                    @else
                        <span class="ml-2 text-[10px] px-2 py-0.5 bg-slate-100 text-slate-600 rounded-full">
                            Admin
                        </span>
                    @endif
                </a>

                <!-- Desktop Nav -->
                <div class="hidden sm:flex sm:ml-10 space-x-1">
                    @foreach($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                           class="flex items-center px-3 py-2 rounded-lg text-sm font-medium transition
                           {{ request()->routeIs($item['route']) ? 'bg-slate-100 text-slate-900' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>

            </div>

            <!-- RIGHT: Actions -->
            <div class="hidden sm:flex items-center space-x-3">

                {{-- NOTIFICATION BELL (all users) --}}
                @php $unreadCount = $user->unreadNotifications()->count(); @endphp
                <x-dropdown align="right" width="80">
                    <x-slot name="trigger">
                        <button class="relative p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                                </path>
                            </svg>
                            @if($unreadCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-rose-500 text-[9px] font-black text-white ring-2 ring-white">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                            @endif
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                            <p class="text-xs font-black text-slate-900 uppercase tracking-widest">Notifications</p>
                            @if($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                @csrf
                                <button class="text-[10px] font-bold text-slate-400 hover:text-slate-700 uppercase tracking-widest transition-colors">
                                    Mark all read
                                </button>
                            </form>
                            @endif
                        </div>

                        @php
                            $recentNotifications = $user->notifications()->latest()->take(6)->get();
                        @endphp

                        @forelse($recentNotifications as $notification)
                        @php
                            $data = $notification->data;
                            $icon = $data['icon'] ?? 'bell';
                            $iconMap = [
                                'quote'   => ['path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'text-indigo-500 bg-indigo-50'],
                                'payment' => ['path' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', 'color' => 'text-emerald-500 bg-emerald-50'],
                                'client'  => ['path' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'color' => 'text-amber-500 bg-amber-50'],
                                'invoice' => ['path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'color' => 'text-sky-500 bg-sky-50'],
                                'bell'    => ['path' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'color' => 'text-slate-500 bg-slate-100'],
                            ];
                            $ic = $iconMap[$icon] ?? $iconMap['bell'];
                        @endphp
                        <a href="{{ $data['url'] ?? route('notifications.index') }}"
                           class="flex items-start gap-3 px-4 py-3 hover:bg-slate-50 transition-colors {{ $notification->read_at ? 'opacity-60' : '' }}">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg {{ $ic['color'] }} flex items-center justify-center mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $ic['path'] }}"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-900 leading-tight">{{ $data['title'] ?? 'Notification' }}</p>
                                <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-2">{{ $data['body'] ?? '' }}</p>
                                <p class="text-[10px] text-slate-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                            @if(!$notification->read_at)
                            <span class="flex-shrink-0 w-2 h-2 rounded-full bg-rose-500 mt-1.5"></span>
                            @endif
                        </a>
                        @empty
                        <div class="px-4 py-6 text-center">
                            <p class="text-xs text-slate-400">No notifications yet</p>
                        </div>
                        @endforelse

                        <div class="border-t border-slate-100 px-4 py-2">
                            <a href="{{ route('notifications.index') }}"
                               class="block text-center text-[11px] font-bold text-slate-500 hover:text-slate-900 uppercase tracking-widest transition-colors">
                                View all notifications
                            </a>
                        </div>
                    </x-slot>
                </x-dropdown>

                <!-- Workspace Dropdown -->
                <x-dropdown align="right" width="64">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-slate-200 rounded-full text-xs font-bold text-slate-600 hover:bg-slate-50 bg-white shadow-sm">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
                            {{ $companyName }}
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase">
                            Workspace
                        </div>

                        <x-dropdown-link href="{{ route('companies.index') }}">
                            Organization Settings
                        </x-dropdown-link>

                        <x-dropdown-link href="{{ route('invoices.index') }}">
                            Billing & Usage
                        </x-dropdown-link>
                    </x-slot>
                </x-dropdown>

                <!-- User Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex rounded-full overflow-hidden ring-1 ring-slate-200 hover:ring-brand-gold">
                            <img class="h-8 w-8 object-cover" src="{{ $user->profile_photo_url }}">
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b">
                            <p class="text-sm font-bold text-slate-900">{{ $user->name }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                        </div>

                        <x-dropdown-link href="{{ route('profile.show') }}">
                            Account
                        </x-dropdown-link>

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

            <!-- MOBILE BUTTON -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="p-2 rounded-lg bg-slate-50">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor">
                        <path x-show="!open" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="open" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

        </div>
    </div>

    <!-- MOBILE MENU -->
    <div x-show="open" x-transition @click.outside="open = false"
         class="sm:hidden border-t bg-white px-4 pb-4 space-y-2">

        @foreach($navItems as $item)
            <a href="{{ route($item['route']) }}"
               class="block px-3 py-2 rounded-lg text-sm font-medium
               {{ request()->routeIs($item['route']) ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}">
                {{ $item['label'] }}
            </a>
        @endforeach

        <div class="border-t pt-3 mt-3">
            <a href="{{ route('profile.show') }}" class="block px-3 py-2 text-sm text-slate-600">
                Account
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full text-left px-3 py-2 text-sm text-red-600">
                    Sign Out
                </button>
            </form>
        </div>

    </div>
</nav>