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
        @keyframes xqToastIn  { from{opacity:0;transform:translateY(10px) scale(.97)} to{opacity:1;transform:none} }
        @keyframes xqSlideIn  { from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:none} }

        /* Sidebar active nav item — left indicator + subtle glow */
        aside nav a.bg-slate-800 {
            border-left: 2px solid #0078D4;
            padding-left: calc(0.75rem - 2px) !important;
            background-image: linear-gradient(90deg, rgba(0,120,212,.14) 0%, transparent 55%);
        }

        /* Sidebar section label */
        aside nav p.text-\[#D4AF37\] {
            letter-spacing: .08em;
        }

        /* Toast entrance */
        .xq-toast { animation: xqToastIn .3s cubic-bezier(.22,1,.36,1) forwards; }

        /* Notification dropdown polish */
        [x-show="openNotifications"] {
            animation: xqSlideIn .2s cubic-bezier(.22,1,.36,1) forwards;
        }

        /* Topbar header blur depth */
        header.sticky { box-shadow: 0 1px 0 rgba(0,120,212,.08), 0 4px 20px rgba(0,0,0,.15); }
    </style>
</head>

<body class="font-sans antialiased bg-slate-950 text-slate-100">
<x-demo-banner />
<div class="min-h-screen flex">

    <!-- Sidebar (desktop + mobile off-canvas) -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 transform -translate-x-full lg:translate-x-0 lg:static bg-slate-900 border-r border-slate-800 flex flex-col transition-transform duration-200">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between" style="background:linear-gradient(135deg,rgba(0,120,212,.1) 0%,transparent 60%)">
            <div class="flex items-center gap-2.5 min-w-0">
                <img src="/img/android-icon-96x96.png" alt="Xquisite" class="h-8 w-8 object-contain shrink-0 rounded-lg">
                <div class="min-w-0">
                    <span class="text-base font-bold tracking-wide text-white" style="font-family:'Montserrat',sans-serif">XQUISITE <span class="text-[#D4AF37]">CREATIONS</span></span>
                    <p class="text-[10px] text-slate-500 mt-0.5 tracking-wide italic" style="font-family:'Montserrat',sans-serif">Understand Your Why.</p>
                </div>
            </div>
            <button id="sidebar-close-btn" class="lg:hidden -mr-1 p-2 rounded-lg text-slate-500 hover:text-white hover:bg-slate-800 transition-colors" aria-label="Close menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto">
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
                $analyticsRoutes = ['analytics.*'];

                $propertyPropertiesRoutes = ['properties.*'];
                $propertyRentersRoutes = ['renters.*'];
                $propertyLeasesRoutes = ['leases.*'];
                $propertyRentPaymentsRoutes = ['rent-payments.*'];
                $propertyMaintenanceRoutes = ['maintenance.*'];

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

                $notificationCount = $unreadNotificationCount;

                $systemMonitoringRoutes = ['monitoring.*'];
                $systemTenantsRoutes = ['admin.tenants.*'];
                $systemModuleRequestsRoutes = ['admin.module-requests.*'];
                $systemUsersRoutes = ['admin.users.*'];
                $systemReviewsRoutes = ['admin.reviews.*'];
                $systemSyncRoutes = ['admin.sync.*'];
                $systemLogsRoutes = ['admin.logs.*'];
            @endphp
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            {{-- Booking module --}}
            @if($authTenant && $authTenant->hasModule('booking'))
                <div class="pt-2 border-t border-slate-800 mt-2">
                    <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">Bookings</p>
                    
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
                </div>
            @endif

            {{-- POS module --}}
            @if($authTenant && $authTenant->hasModule('pos'))
                <div class="pt-2 border-t border-slate-800 mt-2">
                    <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">POS</p>
                    <a href="{{ route('pos.terminal') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posTerminalRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        POS Terminal
                    </a>
                    <a href="{{ route('pos.sales.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posSalesRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        Sales
                    </a>
                    <a href="{{ route('payment-plans.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posPaymentPlansRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Laybys & Plans
                    </a>
                    <a href="{{ route('quotes.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posQuotesRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Quotes
                    </a>
                    <a href="{{ route('products.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posProductsRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Products
                    </a>
                    <a href="{{ route('rental-orders.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posRentalRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                        Rentals
                    </a>
                </div>

                <div class="pt-2 border-t border-slate-800 mt-2">
                    <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">Suppliers</p>
                    <a href="{{ route('suppliers.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($posSuppliersRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                        Suppliers
                    </a>
                </div>

                <div class="pt-2 border-t border-slate-800 mt-2">
                    <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">Inventory</p>
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
                </div>
            @endif

            {{-- E-commerce module --}}
            @if($authTenant && $authTenant->hasModule('ecommerce'))
                <div class="pt-2 border-t border-slate-800 mt-2">
                    <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">E-commerce</p>
                    <a href="{{ route('orders.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($ecommerceRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Orders
                    </a>
                    @if($authTenant)
                        <a href="{{ $authTenant->storefront_url }}" target="_blank"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            View Store
                        </a>
                    @endif
                </div>
            @endif

            {{-- Analytics module --}}
            @if($authTenant && $authTenant->hasModule('analytics'))
                <div class="pt-2 border-t border-slate-800 mt-2">
                    <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">Reporting</p>
                    <a href="{{ route('analytics.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($analyticsRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Analytics
                    </a>
                </div>
            @endif

            {{-- Property Management module --}}
            @if($authTenant && $authTenant->hasModule('property_management'))
                <div class="pt-2 border-t border-slate-800 mt-2">
                    <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">Property</p>
                    <a href="{{ route('properties.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($propertyPropertiesRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Properties
                    </a>
                    <a href="{{ route('renters.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($propertyRentersRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Renters
                    </a>
                    <a href="{{ route('leases.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($propertyLeasesRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Leases
                    </a>
                    <a href="{{ route('rent-payments.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($propertyRentPaymentsRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Rent Payments
                    </a>
                    <a href="{{ route('maintenance.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($propertyMaintenanceRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Maintenance
                    </a>
                </div>
            @endif

            {{-- Clients + Messaging --}}
            @if($authTenant && !Auth::user()->isClient())
                <div class="pt-2 border-t border-slate-800 mt-2">
                    <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">Clients</p>
                    <a href="{{ route('clients.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('clients.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Clients
                    </a>
                </div>
            @endif

            {{-- Client portal nav --}}
            @if(Auth::user()->isClient())
                <div class="pt-2 border-t border-slate-800 mt-2">
                    <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">My Portal</p>
                    <a href="{{ route('portal.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('portal.dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('portal.messages') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('portal.messages') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        Messages
                    </a>
                </div>
            @endif

            {{-- Settings + Admin --}}
            <div class="pt-2 border-t border-slate-800 mt-2">
                <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">Settings</p>
                @if($authTenant)
                    <a href="{{ route('settings.modules.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($settingsModulesRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        My Modules
                    </a>
                    <a href="{{ route('settings.services.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($settingsServicesRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Services
                    </a>
                @endif
                <a href="{{ route('profile.edit') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($profileRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    Account
                </a>
                {{-- Billing (visible to tenant owners only) --}}
                @if(Auth::user()->isOwner() && $authTenant)
                    <a href="{{ route('billing.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('billing.*') && !request()->routeIs('admin.billing.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        Billing
                    </a>
                @endif

                @can('manage-tenants')
                    <div class="pt-2 border-t border-slate-800 mt-2">
                        <p class="px-3 text-xs text-[#D4AF37] uppercase tracking-wide mb-1">System Owner</p>
                        <a href="{{ route('monitoring.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($systemMonitoringRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4 6h16M4 18h16M4 12h16"/></svg>
                            Monitoring
                        </a>
                        <a href="{{ route('admin.tenants.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($systemTenantsRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            Tenants
                        </a>
                        <a href="{{ route('admin.module-requests.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($systemModuleRequestsRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Module Requests
                        </a>
                        <a href="{{ route('admin.platform-modules.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('admin.platform-modules.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            Platform Modules
                        </a>
                        <a href="{{ route('admin.plans.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('admin.plans.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Plans
                        </a>
                        <a href="{{ route('admin.platform-services.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('admin.platform-services.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            Platform Services
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($systemUsersRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                            Users
                        </a>
                        <a href="{{ route('admin.team-members.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('admin.team-members.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-4-4h-3m-4 6H7a4 4 0 01-4-4v-2a4 4 0 014-4h1m4 0a4 4 0 100-8 4 4 0 000 8z"/></svg>
                            Team Members
                        </a>
                        <a href="{{ route('admin.reviews.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($systemReviewsRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            Reviews
                        </a>
                        <a href="{{ route('admin.sync.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($systemSyncRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Sync Queue
                        </a>
                        <a href="{{ route('admin.logs.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs($systemLogsRoutes) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Logs
                        </a>
                        <a href="{{ route('admin.billing.index') }}"
                           class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-800 {{ request()->routeIs('admin.billing.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            Platform Billing
                        </a>
                    </div>
                @endif
            </div>
        </nav>

        <div class="px-3 py-4 border-t border-slate-800">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-8 h-8 rounded-full bg-[#0078D4] flex items-center justify-center text-xs font-bold">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
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

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Top bar (mobile + breadcrumb) -->
        <header class="h-14 flex items-center justify-between px-3 sm:px-6 border-b border-slate-800 bg-slate-900 lg:bg-slate-950/60 lg:backdrop-blur-sm sticky top-0 z-20">
            <div class="flex items-center gap-3">
                <button id="sidebar-open-btn" class="lg:hidden inline-flex items-center justify-center p-2 rounded-md text-slate-300 hover:bg-slate-800" aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="text-sm font-semibold text-[#D4AF37]">
                    @isset($header){{ $header }}@endisset
                </div>
            </div>
            <div class="flex items-center gap-2">
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

        <!-- Mobile sidebar overlay -->
        <div id="mobile-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden"></div>

        <!-- Page Content -->
        <main class="flex-1 p-4 sm:p-6">
            @if(session('success'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-900/50 border border-emerald-700 text-emerald-300 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-red-900/50 border border-red-700 text-red-300 text-sm flex items-center justify-between gap-4">
                    <span>{{ session('error') }}</span>
                    @if(str_contains(session('error', ''), 'suspended'))
                        <a href="{{ route('billing.index') }}" class="shrink-0 text-xs font-semibold underline hover:no-underline">View Billing →</a>
                    @endif
                </div>
            @endif
            @if(session('info'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-[#0078D4]/10 border border-[#0078D4]/30 text-[#0078D4] text-sm">
                    {{ session('info') }}
                </div>
            @endif
            @if(session('warning'))
                <div class="mb-4 px-4 py-3 rounded-lg bg-amber-900/30 border border-amber-700 text-amber-300 text-sm">
                    {{ session('warning') }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

<x-whatsapp-button />
<script>
    (function(){
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-sidebar-overlay');
        const openBtn = document.getElementById('sidebar-open-btn');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            sidebar.classList.add('translate-x-0');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebar() {
            sidebar.classList.remove('translate-x-0');
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        const closeBtn = document.getElementById('sidebar-close-btn');
        if (openBtn)  openBtn.addEventListener('click', openSidebar);
        if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
        if (overlay)  overlay.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeSidebar(); });
    })();
</script>
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
</body>
</html>
