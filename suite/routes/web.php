<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\PaymentPlanController;
use App\Http\Controllers\PublicQuoteController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Booking\AppointmentController;
use App\Http\Controllers\Booking\CustomerController;
use App\Http\Controllers\Booking\ServiceController;
use App\Http\Controllers\Booking\StaffController;
use App\Http\Controllers\POS\PosController;
use App\Http\Controllers\POS\SaleController;
use App\Http\Controllers\POS\ProductController;
use App\Http\Controllers\POS\StockController;
use App\Http\Controllers\POS\PurchaseOrderController;
use App\Http\Controllers\POS\SupplierController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Ecommerce\StorefrontController;
use App\Http\Controllers\Ecommerce\CartController;
use App\Http\Controllers\Ecommerce\CheckoutController;
use App\Http\Controllers\Ecommerce\OrderController;
use App\Http\Controllers\Ecommerce\StoreSettingsController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\PlatformServiceController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ModuleRequestController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\SyncQueueController;
use App\Http\Controllers\Booking\PublicBookingController;
use App\Http\Controllers\Booking\CustomerAuthController;
use App\Http\Controllers\Booking\CustomerPortalController;
use App\Http\Controllers\Property\PropertyController;
use App\Http\Controllers\Property\UnitController;
use App\Http\Controllers\Property\RenterController;
use App\Http\Controllers\Property\LeaseController;
use App\Http\Controllers\Property\RentPaymentController;
use App\Http\Controllers\Property\MaintenanceController;
use App\Http\Controllers\Property\RenterAuthController;
use App\Http\Controllers\Property\RenterPortalController;
use App\Http\Controllers\Admin\PlatformModuleController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Settings\ModuleController;
use App\Http\Controllers\ServiceComboController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\BookingMenuController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\BillingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (request()->user() !== null) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('welcome');

Route::get('/about',   AboutController::class)->name('about');
Route::get('/terms',   fn() => view('terms'))->name('terms');
Route::get('/privacy', fn() => view('privacy'))->name('privacy');

Route::middleware(['auth', 'verified', 'enforce-password-change'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Service combos
    Route::resource('combos', ServiceComboController::class);
    Route::post('combos/{combo}/toggle', [ServiceComboController::class, 'toggle'])->name('combos.toggle');

    // Promotions
    Route::get('promotions/generate-code', [PromotionController::class, 'generateCode'])->name('promotions.generate-code');
    Route::resource('promotions', PromotionController::class);
    Route::post('promotions/{promotion}/toggle', [PromotionController::class, 'toggle'])->name('promotions.toggle');

    // Service categories
    Route::resource('service-categories', ServiceCategoryController::class);
    Route::get('api/service-categories', [ServiceCategoryController::class, 'apiList'])->name('api.service-categories');

    // Booking menu
    Route::get('/booking', [BookingMenuController::class, 'menu'])->name('booking.menu');

    // Clients + messaging
    Route::resource('clients', ClientController::class);
    Route::get('clients/{client}/messages', [CommunicationController::class, 'thread'])->name('clients.messages');
    Route::post('clients/{client}/messages', [CommunicationController::class, 'store'])->name('clients.messages.store');

    // Client portal (for users with role=client)
    Route::get('/portal/dashboard', [ClientPortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::get('/portal/messages', [CommunicationController::class, 'clientIndex'])->name('portal.messages');
    Route::post('/portal/messages/reply', [CommunicationController::class, 'clientReply'])->name('portal.messages.reply');

    // Platform billing (tenant owner view)
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::patch('billing/info', [BillingController::class, 'updateBillingInfo'])->name('billing.info.update');
    Route::post('billing/invoices/{invoice}/pop', [BillingController::class, 'uploadPop'])->name('billing.pop.upload');
    Route::get('billing/invoices/{invoice}/pop', [BillingController::class, 'downloadPop'])->name('billing.pop.download');
    Route::get('billing/invoices/{invoice}/pdf', [BillingController::class, 'downloadPdf'])->name('billing.pdf');
    Route::get('billing/{invoice}', [BillingController::class, 'show'])->name('billing.show');

    // Booking module — gated behind module:booking
    Route::middleware('module:booking')->group(function () {
        // Staff dashboard (realtime-ready)
        Route::get('staff/dashboard', [\App\Http\Controllers\Booking\StaffDashboardController::class, 'index'])->name('staff.dashboard');

        Route::resource('appointments', AppointmentController::class);
        Route::get('appointments/{appointment}/availability', [\App\Http\Controllers\Booking\AppointmentController::class, 'availability'])->name('appointments.availability');
        Route::post('appointments/{appointment}/assign', [\App\Http\Controllers\Booking\AppointmentController::class, 'assign'])->name('appointments.assign');
        Route::post('appointments/{appointment}/remind', [\App\Http\Controllers\Booking\AppointmentController::class, 'remind'])->name('appointments.remind');
        Route::post('appointments/{appointment}/mark-paid', [\App\Http\Controllers\Booking\AppointmentController::class, 'markPaid'])->name('appointments.mark-paid');
        Route::get('calendar/{date?}', [\App\Http\Controllers\Booking\AppointmentController::class, 'calendar'])->name('appointments.calendar');
        Route::resource('customers', CustomerController::class);
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::resource('staff', StaffController::class);
        Route::get('staff/{staff}/schedule', [\App\Http\Controllers\Booking\StaffScheduleController::class, 'edit'])->name('staff.schedule');
        Route::put('staff/{staff}/schedule', [\App\Http\Controllers\Booking\StaffScheduleController::class, 'update'])->name('staff.schedule.update');
        Route::post('staff/{staff}/blocks', [\App\Http\Controllers\Booking\StaffScheduleController::class, 'storeBlock'])->name('staff.blocks.store');
        Route::delete('staff/{staff}/blocks/{block}', [\App\Http\Controllers\Booking\StaffScheduleController::class, 'destroyBlock'])->name('staff.blocks.destroy');
    });

    // POS module — gated behind module:pos
    Route::middleware('module:pos')->group(function () {
        Route::get('/pos', [PosController::class, 'terminal'])->name('pos.terminal');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::post('/pos/layby', [PosController::class, 'layby'])->name('pos.layby');

        Route::prefix('pos/sales')->name('pos.sales.')->group(function () {
            Route::get('/', [SaleController::class, 'index'])->name('index');
            Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
            Route::post('/{sale}/void', [SaleController::class, 'void'])->name('void');
        });

        // Product management
        Route::resource('products', ProductController::class)->except(['show']);

        // Stock management
        Route::get('/stock/take', [StockController::class, 'takePage'])->name('stock.take');
        Route::post('/stock/take', [StockController::class, 'saveStockTake'])->name('stock.take.save');
        Route::get('/stock/reorder-alerts', [StockController::class, 'reorderAlerts'])->name('stock.reorder-alerts');
        Route::post('/products/{product}/stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
        Route::get('/products/{product}/stock/history', [StockController::class, 'history'])->name('stock.history');

        // Purchase orders
        Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
        Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
        Route::post('/purchase-orders/{purchaseOrder}/send', [PurchaseOrderController::class, 'send'])->name('purchase-orders.send');
        Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
        Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

        // Rental orders (decor / event items)
        Route::resource('rental-orders', \App\Http\Controllers\POS\RentalOrderController::class)->except(['edit','update']);
        Route::patch('/rental-orders/{rentalOrder}/out',    [\App\Http\Controllers\POS\RentalOrderController::class, 'markOut'])->name('rental-orders.out');
        Route::patch('/rental-orders/{rentalOrder}/return', [\App\Http\Controllers\POS\RentalOrderController::class, 'returnItem'])->name('rental-orders.return');

        // Suppliers
        Route::resource('suppliers', SupplierController::class);
    });

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // E-commerce — admin orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index')->middleware('module:ecommerce');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show')->middleware('module:ecommerce');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status')->middleware('module:ecommerce');

    // E-commerce — store settings (shipping, etc.)
    Route::get('/store/settings', [StoreSettingsController::class, 'edit'])->name('store.settings')->middleware('module:ecommerce');
    Route::patch('/store/settings', [StoreSettingsController::class, 'update'])->name('store.settings.update')->middleware('module:ecommerce');

    // Property management module
    Route::middleware('module:property_management')->group(function () {
        Route::resource('properties', PropertyController::class);
        Route::resource('properties.units', UnitController::class);
        Route::resource('renters', RenterController::class);
        Route::post('renters/{renter}/invite', [RenterController::class, 'invite'])->name('renters.invite');
        Route::resource('leases', LeaseController::class)->except(['destroy']);
        Route::post('leases/{lease}/terminate', [LeaseController::class, 'terminate'])->name('leases.terminate');
        Route::get('rent-payments', [RentPaymentController::class, 'index'])->name('rent-payments.index');
        Route::get('rent-payments/{rentPayment}', [RentPaymentController::class, 'show'])->name('rent-payments.show');
        Route::patch('rent-payments/{rentPayment}/record', [RentPaymentController::class, 'record'])->name('rent-payments.record');
        Route::post('rent-payments/generate', [RentPaymentController::class, 'generateMonthly'])->name('rent-payments.generate');
        Route::post('rent-payments/flag-overdue', [RentPaymentController::class, 'flagOverdue'])->name('rent-payments.flag-overdue');
        Route::resource('maintenance', MaintenanceController::class);
        Route::patch('maintenance/{maintenance}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenance.status');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        // Platform billing admin (system admin only — auth checked in controller)
        Route::get('billing', [BillingController::class, 'adminIndex'])->name('billing.index');
        // Specific routes MUST come before billing/{tenant} wildcard
        Route::post('billing/batch-generate', [BillingController::class, 'adminBatchGenerate'])->name('billing.batch-generate');
        Route::get('billing/settings', [BillingController::class, 'adminSettings'])->name('billing.settings');
        Route::post('billing/settings', [BillingController::class, 'adminSettingsSave'])->name('billing.settings.save');
        Route::get('billing/{tenant}', [BillingController::class, 'adminShow'])->name('billing.show');
        Route::post('billing/{tenant}/generate', [BillingController::class, 'adminGenerateInvoice'])->name('billing.generate');
        Route::post('billing/{tenant}/suspend', [BillingController::class, 'adminSuspend'])->name('billing.suspend');
        Route::post('billing/{tenant}/reactivate', [BillingController::class, 'adminReactivate'])->name('billing.reactivate');
        Route::post('billing/invoices/{invoice}/mark-paid', [BillingController::class, 'adminMarkPaid'])->name('billing.mark-paid');
        Route::get('billing/invoices/{invoice}/pop', [BillingController::class, 'adminDownloadPop'])->name('billing.pop.admin-download');
        Route::get('billing/invoices/{invoice}/pdf', [BillingController::class, 'adminDownloadPdf'])->name('billing.pdf.admin');

        Route::middleware('can:manage-tenants')->group(function () {
            Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
            Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
            Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
            Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
            Route::post('/tenants/{tenant}/module', [TenantController::class, 'toggleModule'])->name('tenants.module');
            Route::patch('/tenants/{tenant}/subdomain', [TenantController::class, 'updateSubdomain'])->name('tenants.subdomain');
            Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
            Route::post('/tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
            Route::post('/tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
            Route::post('/tenants/{tenant}/users/{user}/reset-password', [TenantController::class, 'resetUserPassword'])->name('tenants.users.reset-password');
            Route::post('/tenants/{tenant}/users/{user}/toggle', [TenantController::class, 'toggleUserStatus'])->name('tenants.users.toggle');
            // Platform ↔ tenant messaging
            Route::get('/tenants/{tenant}/messages', [CommunicationController::class, 'platformThread'])->name('tenants.messages');
            Route::post('/tenants/{tenant}/messages', [CommunicationController::class, 'platformStore'])->name('tenants.messages.store');

        // Billing sync queue
        Route::get('/sync-queue', [SyncQueueController::class, 'index'])->name('sync.index');
        Route::post('/sync-queue/{syncQueue}/retry', [SyncQueueController::class, 'retryOne'])->name('sync.retry');
        Route::post('/sync-queue/retry-all', [SyncQueueController::class, 'retryAll'])->name('sync.retry-all');
        Route::patch('/sync-queue/{syncQueue}/dismiss', [SyncQueueController::class, 'dismiss'])->name('sync.dismiss');

        // Logs
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('/logs/audit', [LogController::class, 'audit'])->name('logs.audit');
        Route::get('/logs/combined', [LogController::class, 'combined'])->name('logs.combined');
        Route::post('/logs/bulk', [LogController::class, 'bulkUpdate'])->name('logs.bulk');
        Route::post('/logs/resolve-all', [LogController::class, 'resolveAll'])->name('logs.resolve-all');
        Route::get('/logs/{log}', [LogController::class, 'show'])->name('logs.show');
        Route::patch('/logs/{log}/status', [LogController::class, 'updateStatus'])->name('logs.status');
            // Platform service catalog + order management
            Route::get('/platform-services', [PlatformServiceController::class, 'index'])->name('platform-services.index');
            Route::get('/platform-services/create', [PlatformServiceController::class, 'create'])->name('platform-services.create');
            Route::post('/platform-services', [PlatformServiceController::class, 'store'])->name('platform-services.store');
            Route::get('/platform-services/{platformService}/edit', [PlatformServiceController::class, 'edit'])->name('platform-services.edit');
            Route::put('/platform-services/{platformService}', [PlatformServiceController::class, 'update'])->name('platform-services.update');
            Route::patch('/service-orders/{order}', [PlatformServiceController::class, 'updateOrder'])->name('service-orders.update');

            // Review moderation
            Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
            Route::patch('/reviews/{review}/status', [AdminReviewController::class, 'updateStatus'])->name('reviews.status');
            Route::patch('/reviews/{review}/featured', [AdminReviewController::class, 'toggleFeatured'])->name('reviews.featured');

            Route::get('/module-requests', [ModuleRequestController::class, 'index'])->name('module-requests.index');
            Route::patch('/module-requests/{moduleRequest}/approve', [ModuleRequestController::class, 'approve'])->name('module-requests.approve');
            Route::patch('/module-requests/{moduleRequest}/reject', [ModuleRequestController::class, 'reject'])->name('module-requests.reject');

            // Team members (about page)
            Route::resource('team-members', TeamMemberController::class)->except(['show']);

            // Bundle plans
            Route::resource('plans', PlanController::class)->except(['show']);
            // Platform module registry
            Route::resource('platform-modules', PlatformModuleController::class)->except(['show', 'destroy']);
            Route::patch('platform-modules/{platformModule}/status', [PlatformModuleController::class, 'updateStatus'])->name('platform-modules.status');
        });

        // User management (for tenant owners to manage their staff)
        Route::resource('users', UserManagementController::class);
        Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivate'])->name('users.deactivate');
        Route::post('/users/{user}/activate', [UserManagementController::class, 'activate'])->name('users.activate');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
        Route::patch('/users/{user}/restore', [UserManagementController::class, 'restore'])->name('users.restore')->withTrashed();
    });

    // Instance Monitoring (owner-level admin)
    Route::middleware('can:manage-tenants')->group(function () {
        Route::resource('monitoring', MonitoringController::class);
    });

    // Settings — self-serve module management
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::post('/modules/request', [ModuleController::class, 'request'])->name('modules.request');
    });

    // Payment plans (layby + event deposits)
    Route::get('/payment-plans', [PaymentPlanController::class, 'index'])->name('payment-plans.index');
    Route::get('/payment-plans/create', [PaymentPlanController::class, 'create'])->name('payment-plans.create');
    Route::post('/payment-plans', [PaymentPlanController::class, 'storePlan'])->name('payment-plans.store');
    Route::get('/payment-plans/{paymentPlan}', [PaymentPlanController::class, 'show'])->name('payment-plans.show');
    Route::patch('/payment-plans/{paymentPlan}/cancel', [PaymentPlanController::class, 'cancel'])->name('payment-plans.cancel');
    Route::post('/payment-plans/installments/{installment}/pay', [PaymentPlanController::class, 'recordPayment'])->name('payment-plans.pay');

    // Quotes
    Route::resource('quotes', QuoteController::class)->except(['edit', 'update']);
    Route::post('/quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');

    // Reviews / feedback
    Route::get('/feedback', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/dismiss', [ReviewController::class, 'dismiss'])->name('reviews.dismiss');

    // Service requests (customer-facing)
    Route::get('/settings/services', [ServiceRequestController::class, 'index'])->name('settings.services.index');
    Route::post('/settings/services', [ServiceRequestController::class, 'store'])->name('settings.services.request');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('book/{slug}')->name('book.')->group(function () {

    Route::get('/',          [PublicBookingController::class, 'index'])->name('index');
    Route::get('/schedule',  [PublicBookingController::class, 'service'])->name('service'); // was /services/{service}
    Route::get('/slots',     [PublicBookingController::class, 'slots'])->name('slots');

    // ── Customer auth routes (guests only — redirect if already logged in) ───
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login',              [CustomerAuthController::class, 'showLogin'])->name('login');
        Route::post('/login',             [CustomerAuthController::class, 'login'])->name('login.post');
        Route::get('/register',           [CustomerAuthController::class, 'showRegister'])->name('register');
        Route::post('/register',          [CustomerAuthController::class, 'register'])->name('register.post');
    });

    // ── Requires customer auth ────────────────────────────────────────────────
    Route::middleware('auth:customer')->group(function () {
        Route::get('/confirm',                                          [PublicBookingController::class, 'confirm'])->name('confirm');
        Route::post('/book',                                            [PublicBookingController::class, 'store'])->name('store');
        Route::post('/promo/check',                                     [PublicBookingController::class, 'checkPromo'])->name('promo.check');
        Route::get('/edit/{appointment}',                               [PublicBookingController::class, 'edit'])->name('edit');
        Route::patch('/update/{appointment}',                           [PublicBookingController::class, 'updateBooking'])->name('update');
        Route::get('/success/{appointment}',                            [PublicBookingController::class, 'success'])->name('success');
        Route::post('/logout',                                          [CustomerAuthController::class, 'logout'])->name('logout');

        // Customer portal
        Route::get('/my-bookings',                                      [CustomerPortalController::class, 'myBookings'])->name('my-bookings');
        Route::get('/notifications',                                    [CustomerPortalController::class, 'notifications'])->name('notifications');
        Route::post('/notifications/read-all',                          [CustomerPortalController::class, 'markNotificationsRead'])->name('notifications.read-all');
        Route::patch('/appointments/{appointment}/cancel',              [CustomerPortalController::class, 'cancel'])->name('cancel');
    });
});

// ─── Renter portal (/rent/{slug}) ────────────────────────────────────────────
Route::prefix('rent/{slug}')->name('rent.')->group(function () {
    // Public auth routes — no guard needed
    Route::get('/login',        [RenterAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',       [RenterAuthController::class, 'login'])->name('login.post');
    Route::post('/logout',      [RenterAuthController::class, 'logout'])->name('logout');

    // Protected portal pages — renter guard enforced at route level
    Route::middleware('auth:renter')->group(function () {
        Route::get('/',             [RenterPortalController::class, 'portal'])->name('portal');
        Route::get('/lease',        [RenterPortalController::class, 'lease'])->name('lease');
        Route::get('/payments',     [RenterPortalController::class, 'payments'])->name('payments');
        Route::get('/maintenance',  [RenterPortalController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance', [RenterPortalController::class, 'submitMaintenance'])->name('maintenance.submit');
    });
});

// Public storefront (no auth)
Route::prefix('shop/{tenantSlug}')->name('shop.')->group(function () {
    Route::get('/', [StorefrontController::class, 'index'])->name('index');
    Route::get('/product/{productId}', [StorefrontController::class, 'product'])->name('product');

    // Cart
    Route::get('/cart', [CartController::class, 'view'])->name('cart');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
    Route::get('/order/{reference}/confirmed', [CheckoutController::class, 'confirmed'])->name('order.confirmed');

    // PayFast callbacks
    Route::post('/payfast/notify', [CheckoutController::class, 'payfastNotify'])->name('payfast.notify')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
    Route::get('/payfast/return', [CheckoutController::class, 'payfastReturn'])->name('payfast.return');
    Route::get('/payfast/cancel', [CheckoutController::class, 'payfastCancel'])->name('payfast.cancel');
});

// ─── Public quote acceptance (no auth) ───────────────────────────────────────
Route::prefix('q/{quote}')->name('public.quotes.')->group(function () {
    Route::get('/{token}',           [PublicQuoteController::class, 'show'])->name('show');
    Route::post('/{token}/accept',   [PublicQuoteController::class, 'accept'])->name('accept');
    Route::get('/{token}/pay',       [PublicQuoteController::class, 'payDeposit'])->name('pay');
    Route::post('/{token}/decline',  [PublicQuoteController::class, 'decline'])->name('decline');
});


Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);

// Health check endpoint — used by the monitoring system
Route::get('/api/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $db = true;
    } catch (\Throwable) {
        $db = false;
    }

    $status = $db ? 'up' : 'down';

    return response()->json([
        'status'    => $status,
        'db'        => $db,
        'timestamp' => now()->toISOString(),
    ], $db ? 200 : 503);
})->name('health');

// Client-side JS error collector — no auth required, rate-limited
Route::post('/js-error', [App\Http\Controllers\JsErrorController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('js.error');


require __DIR__.'/auth.php';
