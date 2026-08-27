<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Xquisite Creations') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=montserrat:500,600,700|inter:400,500,600&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="apple-touch-icon" sizes="57x57" href="/img/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/img/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/img/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/img/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/img/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/img/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
    <link rel="manifest" href="/img/manifest.json">
    <meta name="msapplication-TileColor" content="#002B5B">
    <meta name="msapplication-TileImage" content="/img/ms-icon-144x144.png">
    <meta name="theme-color" content="#002B5B">
    <style>
        /* ─── App UI Polish ───────────────────────────────────────── */
        [x-cloak] { display: none !important; }
        @keyframes xqToastIn  { from{opacity:0;transform:translateY(10px) scale(.97)} to{opacity:1;transform:none} }
        @keyframes xqSlideIn  { from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:none} }
        @keyframes xqFadeIn   { from{opacity:0} to{opacity:1} }

        /* ── Sidebar active nav item — left indicator + blue glow ── */
        aside nav a.bg-slate-800 {
            border-left: 2px solid #0078D4;
            padding-left: calc(0.75rem - 2px) !important;
            background-image: linear-gradient(90deg, rgba(0,120,212,.2) 0%, rgba(0,120,212,.04) 50%, transparent 100%);
            color: white !important;
        }

        /* ── Sidebar section labels — bolder, more scannable ── */
        aside nav p.uppercase {
            font-size: .625rem;
            font-weight: 700;
            letter-spacing: .1em;
            opacity: .75;
            padding-top: .1rem;
        }

        /* ── Sidebar section dividers — more breathing room ── */
        aside nav .border-t {
            border-color: rgba(148,163,184,.12) !important;
            margin-top: .5rem;
            padding-top: .5rem;
        }

        /* ── Toast entrance ── */
        .xq-toast { animation: xqToastIn .3s cubic-bezier(.22,1,.36,1) forwards; }

        /* ── Notification dropdown ── */
        [x-show="openNotifications"] {
            animation: xqSlideIn .2s cubic-bezier(.22,1,.36,1) forwards;
        }

        /* ── Topbar depth ── */
        header.sticky { box-shadow: 0 1px 0 rgba(0,120,212,.1), 0 4px 24px rgba(0,0,0,.2); }

        /* ── Stat cards — number uses tabular figures ── */
        .stat-number { font-variant-numeric: tabular-nums; }

        /* ── Flash messages fade in ── */
        .xq-flash { animation: xqFadeIn .25s ease-out forwards; }
    </style>
</head>

<body class="font-sans antialiased bg-slate-950 text-slate-100">
<x-demo-banner />
<div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
    @php
        $authTenant = Auth::user()->tenant;
        $authTenant?->load('activeModules');

        $bookingRoutes = [
            'appointments.*',
            'customers.*',
            'services.*',
            'staff.*',
            'staff.dashboard',
        ];
        $bookingUrl = $authTenant ? route('book.index', $authTenant->slug) : null;

        $posTerminalRoutes = ['pos.terminal'];
        $posSalesRoutes = ['pos.sales.*'];
        $posPaymentPlansRoutes = ['payment-plans.*'];
        $posQuotesRoutes = ['quotes.*'];
        $posProductsRoutes = ['products.*'];
        $posRentalRoutes = ['rental-orders.*'];
        $posSuppliersRoutes = ['suppliers.*'];
        $posStockTakeRoutes = ['stock.take*'];
        $posStockReorderRoutes = ['stock.reorder-alerts'];
        $posPurchaseOrderRoutes = ['purchase-orders.*'];

        $ecommerceRoutes = ['orders.*'];
        $storeSettingsRoutes = ['store.settings*'];
        $analyticsRoutes = ['analytics.*'];

        $propertyPropertiesRoutes = ['properties.*'];
        $propertyApplicantsRoutes = ['applicants.*'];
        $propertyRentersRoutes = ['renters.*'];
        $propertyLeasesRoutes = ['leases.*'];
        $propertyRentPaymentsRoutes = ['rent-payments.*'];
        $propertyMaintenanceRoutes = ['maintenance.*'];
        $propertyContractorsRoutes = ['contractors.*'];

        $settingsModulesRoutes = ['settings.modules*'];
        $settingsServicesRoutes = ['settings.services*'];
        $profileRoutes = ['profile.*'];

        $unreadNotificationCount = 0;
        $recentNotifications = collect();
        $internalNotifications = collect();

        if (Auth::check()) {
            $unreadNotificationCount = Auth::user()->unreadNotifications()->count();
            $recentNotifications = Auth::user()->notifications()->latest()->take(6)->get();
        }

        $sidebarReorderCount = 0;
        if ($authTenant && $authTenant->hasModule('pos')) {
            $sidebarReorderCount = \App\Modules\POS\Models\Product::where('track_stock', true)
                ->where('reorder_level', '>', 0)
                ->whereColumn('stock_quantity', '<=', 'reorder_level')
                ->count();
        }

        if ($sidebarReorderCount > 0) {
            $internalNotifications->push([
                'id' => 'reorder-alerts',
                'title' => 'Low stock alert',
                'message' => "{$sidebarReorderCount} product".($sidebarReorderCount === 1 ? ' is' : 's are')." below reorder level.",
                'url' => route('stock.reorder-alerts'),
                'created_at' => now(),
            ]);
        }

        $sidebarFlaggedIpCount = 0;
        if (Auth::check() && Auth::user()->can('manage-tenants')) {
            $sidebarFlaggedIpCount = \App\Services\Security\IpReputationService::flaggedUnverifiedCount();
        }

        if ($sidebarFlaggedIpCount > 0) {
            $internalNotifications->push([
                'id' => 'ip-reputation',
                'title' => 'Unusual IP activity',
                'message' => "{$sidebarFlaggedIpCount} IP".($sidebarFlaggedIpCount === 1 ? ' is' : 's are')." shared across more than 3 businesses, unverified.",
                'url' => route('admin.ip-reputation.index'),
                'created_at' => now(),
            ]);
        }

        $notificationCount = $unreadNotificationCount;

        $systemMonitoringRoutes = ['monitoring.*'];
        $systemTenantsRoutes = ['admin.tenants.*'];
        $systemModuleRequestsRoutes = ['admin.module-requests.*'];
        $systemUsersRoutes = ['admin.users.*'];
        $systemReviewsRoutes = ['admin.reviews.*'];
        $systemSyncRoutes = ['admin.sync.*'];
        $systemLogsRoutes = ['admin.logs.*'];

        $bookingActive = $authTenant && $authTenant->hasModule('booking');
        $posActive = $authTenant && $authTenant->hasModule('pos');
        $ecommerceActive = $authTenant && $authTenant->hasModule('ecommerce');
        $analyticsActive = $authTenant && $authTenant->hasModule('analytics');
        $propertyActive = $authTenant && $authTenant->hasModule('property_management');
        $clientsActive = $authTenant && !Auth::user()->isClient();
        $isClientPortal = Auth::user()->isClient();
        $canManageTenants = Auth::user()->can('manage-tenants');

        $bookingIsCurrent = request()->routeIs($bookingRoutes);
        $posIsCurrent = request()->routeIs(array_merge($posTerminalRoutes, $posSalesRoutes, $posPaymentPlansRoutes, $posQuotesRoutes, $posProductsRoutes, $posRentalRoutes));
        $suppliersIsCurrent = request()->routeIs($posSuppliersRoutes);
        $inventoryIsCurrent = request()->routeIs(array_merge($posStockTakeRoutes, $posStockReorderRoutes, $posPurchaseOrderRoutes));
        $ecommerceIsCurrent = request()->routeIs(array_merge($ecommerceRoutes, $storeSettingsRoutes));
        $analyticsIsCurrent = request()->routeIs($analyticsRoutes);
        $propertyIsCurrent = request()->routeIs(array_merge($propertyPropertiesRoutes, $propertyApplicantsRoutes, $propertyRentersRoutes, $propertyLeasesRoutes, $propertyRentPaymentsRoutes, $propertyMaintenanceRoutes, $propertyContractorsRoutes));
        $clientsIsCurrent = request()->routeIs('clients.*');
        $settingsIsCurrent = request()->routeIs(array_merge($settingsModulesRoutes, $settingsServicesRoutes, $profileRoutes, ['billing.*']));
        $systemIsCurrent = request()->routeIs(['monitoring.*', 'admin.tenants.*', 'admin.module-requests.*', 'admin.platform-modules.*', 'admin.plans.*', 'admin.platform-services.*', 'admin.users.*', 'admin.team-members.*', 'admin.reviews.*', 'admin.sync.*', 'admin.logs.*', 'admin.billing.*']);
    @endphp

    {{-- ═══════════════ DESKTOP SIDEBAR — accordion ═══════════════ --}}
    <aside class="hidden lg:flex lg:flex-col w-64 shrink-0 bg-slate-900 border-r border-slate-800">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-2.5" style="background:linear-gradient(135deg,rgba(0,120,212,.1) 0%,transparent 60%)">
            <img src="/img/android-icon-96x96.png" alt="Xquisite" class="h-8 w-8 object-contain shrink-0 rounded-lg">
            <div class="min-w-0">
                <span class="text-base font-bold tracking-wide text-white" style="font-family:'Montserrat',sans-serif">XQUISITE <span class="text-[#D4AF37]">CREATIONS</span></span>
                <p class="text-[10px] text-slate-500 mt-0.5 tracking-wide italic" style="font-family:'Montserrat',sans-serif">Understand Your Why.</p>
            </div>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            @if($bookingActive)
                <x-nav-section label="Bookings" :active="$bookingIsCurrent">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.bookings-links')
                </x-nav-section>
            @endif

            @if($posActive)
                <x-nav-section label="POS" :active="$posIsCurrent">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.pos-links')
                </x-nav-section>

                <x-nav-section label="Suppliers" :active="$suppliersIsCurrent">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.suppliers-links')
                </x-nav-section>

                <x-nav-section label="Inventory" :active="$inventoryIsCurrent">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.inventory-links')
                </x-nav-section>
            @endif

            @if($ecommerceActive)
                <x-nav-section label="E-commerce" :active="$ecommerceIsCurrent">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.ecommerce-links')
                </x-nav-section>
            @endif

            @if($analyticsActive)
                <x-nav-section label="Reporting" :active="$analyticsIsCurrent">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.reporting-links')
                </x-nav-section>
            @endif

            @if($propertyActive)
                <x-nav-section label="Property" :active="$propertyIsCurrent">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.property-links')
                </x-nav-section>
            @endif

            @if($clientsActive)
                <x-nav-section label="Clients" :active="$clientsIsCurrent">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.clients-links')
                </x-nav-section>
            @endif

            @if($isClientPortal)
                <x-nav-section label="My Portal" :active="true">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.client-portal-links')
                </x-nav-section>
            @endif

            <x-nav-section label="Settings" :active="$settingsIsCurrent">
                <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></x-slot:icon>
                @include('layouts.partials.nav.settings-links')
            </x-nav-section>

            @if($canManageTenants)
                <x-nav-section label="System Owner" :active="$systemIsCurrent">
                    <x-slot:icon><svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg></x-slot:icon>
                    @include('layouts.partials.nav.system-admin-links')
                </x-nav-section>
            @endif
        </nav>

        <div class="px-3 py-4 border-t border-slate-800/60">
            <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-slate-800/40">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#0078D4] to-[#005BA1] flex items-center justify-center text-xs font-bold shrink-0 ring-2 ring-slate-700">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-white truncate leading-tight">{{ Auth::user()->name }}</p>
                    @php $roleName = Auth::user()->getRoleNames()->first(); @endphp
                    <p class="text-[10px] text-slate-500 truncate mt-0.5">
                        {{ $roleName ? ucfirst(str_replace('-', ' ', $roleName)) : Auth::user()->email }}
                    </p>
                </div>
            </div>
            <a href="{{ route('reviews.create') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reviews.create') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Give feedback
            </a>
            <form method="POST" action="{{ route('logout') }}" class="mt-1">
                @csrf
                <button type="submit" class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-slate-400 hover:text-white hover:bg-slate-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    {{-- ═══════════════ MOBILE SIDEBAR — icon rail + flyout ═══════════════ --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="lg:hidden fixed inset-0 bg-black/50 z-30" x-transition.opacity></div>

    <aside x-show="sidebarOpen" x-cloak x-data="{ mobileFlyout: null }" @keydown.escape.window="sidebarOpen = false; mobileFlyout = null"
           class="lg:hidden fixed inset-y-0 left-0 z-40 flex flex-col items-center w-[72px] bg-slate-900 border-r border-slate-800 py-4"
           x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">

        <img src="/img/android-icon-96x96.png" alt="Xquisite" class="h-8 w-8 object-contain rounded-lg mb-2 shrink-0">
        <button type="button" @click="sidebarOpen = false" class="w-8 h-8 mb-3 rounded-lg flex items-center justify-center text-slate-500 hover:text-white hover:bg-slate-800 shrink-0" aria-label="Close menu">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <div class="flex-1 flex flex-col gap-1 items-center w-full px-2">
            <a href="{{ route('dashboard') }}"
               class="w-11 h-11 rounded-lg flex items-center justify-center shrink-0 {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}"
               aria-label="Dashboard" title="Dashboard">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </a>

            @if($bookingActive)
                <div class="relative">
                    <x-nav-rail-icon flyout-key="bookings" label="Bookings" :active="$bookingIsCurrent">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="bookings" label="Bookings">
                        @include('layouts.partials.nav.bookings-links')
                    </x-nav-flyout>
                </div>
            @endif

            @if($posActive)
                <div class="relative">
                    <x-nav-rail-icon flyout-key="pos" label="POS" :active="$posIsCurrent">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="pos" label="POS">
                        @include('layouts.partials.nav.pos-links')
                    </x-nav-flyout>
                </div>

                <div class="relative">
                    <x-nav-rail-icon flyout-key="suppliers" label="Suppliers" :active="$suppliersIsCurrent">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="suppliers" label="Suppliers">
                        @include('layouts.partials.nav.suppliers-links')
                    </x-nav-flyout>
                </div>

                <div class="relative">
                    <x-nav-rail-icon flyout-key="inventory" label="Inventory" :active="$inventoryIsCurrent">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="inventory" label="Inventory">
                        @include('layouts.partials.nav.inventory-links')
                    </x-nav-flyout>
                </div>
            @endif

            @if($ecommerceActive)
                <div class="relative">
                    <x-nav-rail-icon flyout-key="ecommerce" label="E-commerce" :active="$ecommerceIsCurrent">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="ecommerce" label="E-commerce">
                        @include('layouts.partials.nav.ecommerce-links')
                    </x-nav-flyout>
                </div>
            @endif

            @if($analyticsActive)
                <div class="relative">
                    <x-nav-rail-icon flyout-key="reporting" label="Reporting" :active="$analyticsIsCurrent">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="reporting" label="Reporting">
                        @include('layouts.partials.nav.reporting-links')
                    </x-nav-flyout>
                </div>
            @endif

            @if($propertyActive)
                <div class="relative">
                    <x-nav-rail-icon flyout-key="property" label="Property" :active="$propertyIsCurrent">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="property" label="Property">
                        @include('layouts.partials.nav.property-links')
                    </x-nav-flyout>
                </div>
            @endif

            @if($clientsActive)
                <div class="relative">
                    <x-nav-rail-icon flyout-key="clients" label="Clients" :active="$clientsIsCurrent">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="clients" label="Clients">
                        @include('layouts.partials.nav.clients-links')
                    </x-nav-flyout>
                </div>
            @endif

            @if($isClientPortal)
                <div class="relative">
                    <x-nav-rail-icon flyout-key="portal" label="My Portal" :active="true">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="portal" label="My Portal">
                        @include('layouts.partials.nav.client-portal-links')
                    </x-nav-flyout>
                </div>
            @endif

            <div class="relative">
                <x-nav-rail-icon flyout-key="settings" label="Settings" :active="$settingsIsCurrent">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                </x-nav-rail-icon>
                <x-nav-flyout flyout-key="settings" label="Settings">
                    @include('layouts.partials.nav.settings-links')
                </x-nav-flyout>
            </div>

            @if($canManageTenants)
                <div class="relative">
                    <x-nav-rail-icon flyout-key="system" label="System Owner" :active="$systemIsCurrent">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </x-nav-rail-icon>
                    <x-nav-flyout flyout-key="system" label="System Owner">
                        @include('layouts.partials.nav.system-admin-links')
                    </x-nav-flyout>
                </div>
            @endif
        </div>

        <div class="relative mt-2">
            <x-nav-rail-icon flyout-key="profile" label="{{ Auth::user()->name }}">
                <span class="w-8 h-8 rounded-full bg-gradient-to-br from-[#0078D4] to-[#005BA1] flex items-center justify-center text-[10px] font-bold ring-2 ring-slate-700">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </span>
            </x-nav-rail-icon>
            <x-nav-flyout flyout-key="profile" :label="Auth::user()->name">
                <a href="{{ route('reviews.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Give feedback
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign out
                    </button>
                </form>
            </x-nav-flyout>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Top bar (mobile + breadcrumb) -->
        <header class="h-14 flex items-center gap-3 px-3 sm:px-6 border-b border-slate-800 bg-slate-900 lg:bg-slate-950/60 lg:backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3 min-w-0 shrink-0">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-300 hover:bg-slate-800" aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="hidden sm:flex items-center gap-2 min-w-0">
                    <span class="text-sm font-semibold text-[#D4AF37] truncate">@isset($header){{ $header }}@endisset</span>
                    @if(Auth::user()->tenant?->name)
                        <span class="hidden lg:inline text-slate-700">·</span>
                        <span class="hidden lg:inline text-xs text-slate-500 truncate">{{ Auth::user()->tenant->name }}</span>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2 ml-auto shrink-0">
                <span class="hidden lg:block text-sm text-slate-400 tabular-nums">{{ now()->format('d M Y') }}</span>
                <div x-data="{ openNotifications: false }" class="relative">
                    <button type="button" @click="openNotifications = !openNotifications"
                            class="relative inline-flex items-center justify-center p-2 rounded-md text-slate-300 hover:bg-slate-800"
                            aria-label="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if($notificationCount > 0)
                            <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-semibold leading-none text-slate-950 bg-amber-400 rounded-full">
                                {{ $notificationCount }}
                            </span>
                        @endif
                    </button>
                    <div x-show="openNotifications" x-cloak @click.away="openNotifications = false"
                         class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-1rem)] bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden z-50 text-left">
                        <div class="px-4 py-3 border-b border-slate-800 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-white">Notifications</p>
                                <p class="text-xs text-slate-500">{{ $notificationCount }} unread</p>
                            </div>
                            <button type="button" @click="openNotifications = false" class="text-slate-400 hover:text-white text-xs">Close</button>
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-800">
                            @if($internalNotifications->count())
                                <div class="px-4 py-3 bg-slate-950 text-slate-400 text-xs uppercase tracking-wide">System alerts</div>
                                @foreach($internalNotifications as $note)
                                    <a href="{{ $note['url'] }}" class="block px-4 py-3 hover:bg-slate-800 text-sm text-slate-200">
                                        <p class="font-semibold text-white">{{ $note['title'] }}</p>
                                        <p class="text-xs text-slate-400 mt-1">{{ $note['message'] }}</p>
                                    </a>
                                @endforeach
                            @endif

                            @forelse($recentNotifications as $notification)
                                @php $data = $notification->data; @endphp
                                <a href="{{ $data['url'] ?? '#' }}" class="block px-4 py-3 hover:bg-slate-800 text-sm text-slate-200">
                                    <p class="font-semibold text-white">{{ $data['title'] ?? class_basename($notification->type) }}</p>
                                    <p class="text-xs text-slate-400 mt-1">{{ $data['message'] ?? 'New update available.' }}</p>
                                    <p class="text-[10px] uppercase tracking-wide text-slate-500 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                                </a>
                            @empty
                                <div class="px-4 py-4 text-sm text-slate-400">
                                    You have no recent notifications.
                                </div>
                            @endforelse
                        </div>
                        <div class="px-4 py-3 border-t border-slate-800">
                            <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ route('notifications.index') }}" class="text-xs text-slate-400 hover:text-white">View all</a>
                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-slate-400 hover:text-white">Mark all read</button>
                                </form>
                            </div>
                            <div class="flex items-center justify-between gap-2">
                                <button type="button" onclick="requestBrowserNotificationPermission()"
                                        class="text-xs text-slate-400 hover:text-white">Enable browser notifications</button>
                                <button type="button" onclick="toggleNotificationSound()"
                                        class="text-xs text-slate-400 hover:text-white">Toggle sound</button>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
                <span class="text-sm text-slate-400 lg:hidden">{{ Auth::user()->name }}</span>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 sm:p-6">
            @if($errors->any())
                <div class="xq-flash mb-4 px-4 py-3 rounded-lg bg-red-900/50 border border-red-700/60 text-red-300 text-sm">
                    <div class="flex items-center gap-2 font-medium">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        Please fix the following:
                    </div>
                    <ul class="mt-1 ml-6 list-disc space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if(session('success'))
                <div class="xq-flash mb-4 px-4 py-3 rounded-lg bg-emerald-900/50 border border-emerald-700/60 text-emerald-300 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="xq-flash mb-4 px-4 py-3 rounded-lg bg-red-900/50 border border-red-700/60 text-red-300 text-sm flex items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                    @if(str_contains(session('error', ''), 'suspended'))
                        <a href="{{ route('billing.index') }}" class="shrink-0 text-xs font-semibold underline hover:no-underline">View Billing →</a>
                    @endif
                </div>
            @endif
            @if(session('info'))
                <div class="xq-flash mb-4 px-4 py-3 rounded-lg bg-[#0078D4]/10 border border-[#0078D4]/30 text-[#0078D4] text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('info') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="xq-flash mb-4 px-4 py-3 rounded-lg bg-yellow-900/30 border border-yellow-700/60 text-yellow-300 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    {{ session('warning') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

{{-- Toast container — referenced by createAppToast() in JS below --}}
<div id="app-notification-toast-container"
     class="fixed bottom-4 right-4 z-[200] flex flex-col gap-2 pointer-events-none w-full max-w-sm">
</div>

<x-whatsapp-button />
    <script>
        (function(){
            const mq = window.matchMedia('(max-width: 640px)');

            function buildSummariesForTable(table) {
                const existing = table.nextElementSibling && table.nextElementSibling.classList && table.nextElementSibling.classList.contains('mobile-table-summaries') ? table.nextElementSibling : null;
                if (existing) existing.remove();

                const tbody = table.querySelector('tbody');
                if (!tbody) return null;

                const rows = Array.from(tbody.querySelectorAll('tr'));
                const container = document.createElement('div');
                container.className = 'mobile-table-summaries space-y-2 sm:hidden mt-3';

                rows.forEach(row => {
                    const cells = Array.from(row.querySelectorAll('td, th'));
                    const title = cells[0] ? cells[0].innerText.trim() : '';
                    const subtitle = cells[1] ? cells[1].innerText.trim() : '';
                    const meta = cells[2] ? cells[2].innerText.trim() : '';

                    const card = document.createElement('a');
                    card.className = 'block bg-slate-900 border border-slate-800 rounded-lg p-3 hover:bg-slate-800 transition-colors';
                    card.href = row.querySelector('a') ? row.querySelector('a').href : 'javascript:void(0)';

                    const h = document.createElement('div');
                    h.className = 'flex items-center justify-between';
                    const t = document.createElement('div');
                    t.className = 'font-medium text-sm text-white';
                    t.textContent = title || '—';
                    const m = document.createElement('div');
                    m.className = 'text-xs text-slate-400';
                    m.textContent = meta || '';
                    h.appendChild(t);
                    h.appendChild(m);

                    const s = document.createElement('div');
                    s.className = 'text-xs text-slate-400 mt-1';
                    s.textContent = subtitle || '';

                    card.appendChild(h);
                    if (subtitle) card.appendChild(s);
                    container.appendChild(card);
                });

                table.parentNode.insertBefore(container, table.nextSibling);
                return container;
            }

            function toggleSummaries() {
                const tables = Array.from(document.querySelectorAll('table.summary-on-mobile'));
                tables.forEach(table => {
                    if (mq.matches) {
                        // mobile: hide native table, show/create summaries
                        table.style.display = 'none';
                        const existing = table.nextElementSibling && table.nextElementSibling.classList && table.nextElementSibling.classList.contains('mobile-table-summaries');
                        if (!existing) buildSummariesForTable(table);
                    } else {
                        // desktop: show table, remove summary container if exists
                        table.style.display = '';
                        const existing = table.nextElementSibling && table.nextElementSibling.classList && table.nextElementSibling.classList.contains('mobile-table-summaries') ? table.nextElementSibling : null;
                        if (existing) existing.remove();
                    }
                });
            }

            mq.addEventListener ? mq.addEventListener('change', toggleSummaries) : mq.addListener(toggleSummaries);
            document.addEventListener('DOMContentLoaded', toggleSummaries);
            window.addEventListener('load', toggleSummaries);
            window.addEventListener('resize', function(){ setTimeout(toggleSummaries, 120); });
        })();

        let notificationSoundEnabled = localStorage.getItem('appNotificationSound');
        if (notificationSoundEnabled === null) {
            notificationSoundEnabled = 'true';
            localStorage.setItem('appNotificationSound', notificationSoundEnabled);
        }
        notificationSoundEnabled = notificationSoundEnabled === 'true';

        function playNotificationSound() {
            if (!notificationSoundEnabled || !('AudioContext' in window || 'webkitAudioContext' in window)) {
                return;
            }

            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                const context = new AudioContext();
                const oscillator = context.createOscillator();
                const gain = context.createGain();

                oscillator.type = 'triangle';
                oscillator.frequency.value = 520;
                gain.gain.value = 0.0001;

                oscillator.connect(gain);
                gain.connect(context.destination);

                oscillator.start();
                gain.gain.exponentialRampToValueAtTime(0.08, context.currentTime + 0.02);
                gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.18);
                oscillator.stop(context.currentTime + 0.18);
            } catch (e) {
                console.warn('Notification sound unavailable', e);
            }
        }

        function createAppToast({ title = 'Notice', message = '', type = 'info', href = null }) {
            const container = document.getElementById('app-notification-toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = 'xq-toast pointer-events-auto w-full max-w-sm rounded-2xl border border-slate-700 bg-slate-900/95 shadow-xl backdrop-blur-lg overflow-hidden';
            toast.innerHTML = `
                <div class="p-4 ${type === 'error' ? 'border-l-4 border-red-500' : type === 'success' ? 'border-l-4 border-emerald-500' : 'border-l-4 border-slate-500'}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-white">${title}</p>
                            <p class="mt-1 text-sm text-slate-400">${message}</p>
                        </div>
                        <button type="button" class="text-slate-400 hover:text-white text-xs" aria-label="Dismiss notification">Dismiss</button>
                    </div>
                </div>
            `;

            const dismissButton = toast.querySelector('button');
            if (dismissButton) {
                dismissButton.addEventListener('click', () => toast.remove());
            }

            if (href) {
                toast.style.cursor = 'pointer';
                toast.addEventListener('click', () => window.location.href = href);
            }

            container.appendChild(toast);
            setTimeout(() => toast.remove(), 7000);
            playNotificationSound();

            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, { body: message });
            }
        }

        window.toggleNotificationSound = function() {
            notificationSoundEnabled = !notificationSoundEnabled;
            localStorage.setItem('appNotificationSound', notificationSoundEnabled ? 'true' : 'false');
            createAppToast({
                title: 'Notification sound',
                message: notificationSoundEnabled ? 'Sound enabled for alerts.' : 'Sound disabled for alerts.',
                type: 'success',
            });
        };

        window.requestBrowserNotificationPermission = function() {
            if (!('Notification' in window)) {
                return createAppToast({ title: 'Notifications unsupported', message: 'Your browser does not support notifications.', type: 'error' });
            }

            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    createAppToast({ title: 'Browser notifications enabled', message: 'New alerts will appear even when the app is in the background.', type: 'success' });
                } else {
                    createAppToast({ title: 'Permission denied', message: 'Browser notifications are disabled.', type: 'error' });
                }
            });
        };
    </script>
    <script>
    /* ── Form submit guard: prevents double-submit on slow networks ── */
    (function () {
        var SPINNER = '<svg class="inline animate-spin w-3.5 h-3.5 mr-1.5 -mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>';

        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (form.dataset.xqBusy) { e.preventDefault(); return; }
            form.dataset.xqBusy = '1';
            var btn = form.querySelector('[type="submit"]');
            if (!btn) return;
            btn.disabled = true;
            btn.dataset.xqOrig = btn.innerHTML;
            btn.innerHTML = SPINNER + (btn.dataset.loading || 'Please wait…');
        }, true);

        /* Re-enable on browser back (bfcache restore) */
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) return;
            document.querySelectorAll('[data-xq-busy]').forEach(function (form) {
                delete form.dataset.xqBusy;
                var btn = form.querySelector('[type="submit"]');
                if (btn && btn.dataset.xqOrig) { btn.disabled = false; btn.innerHTML = btn.dataset.xqOrig; }
            });
        });
    })();
    </script>
    <script>
    window.onerror = function(msg, src, line, col, err) {
        navigator.sendBeacon('{{ route('js.error') }}', new Blob([JSON.stringify({
            _token: '{{ csrf_token() }}', message: String(msg).slice(0,500),
            source: src, line: line, col: col, url: location.href,
            stack: err ? String(err.stack).slice(0,2000) : null
        })], {type:'application/json'}));
    };
    window.addEventListener('unhandledrejection', function(e) {
        navigator.sendBeacon('{{ route('js.error') }}', new Blob([JSON.stringify({
            _token: '{{ csrf_token() }}', message: ('[Promise] ' + (e.reason?.message || String(e.reason))).slice(0,500),
            url: location.href, stack: e.reason?.stack ? String(e.reason.stack).slice(0,2000) : null
        })], {type:'application/json'}));
    });
    </script>
</body>
</html>
