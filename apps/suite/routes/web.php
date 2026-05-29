<?php

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
use App\Http\Controllers\Settings\ModuleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Booking module — gated behind module:booking
    Route::middleware('module:booking')->group(function () {
        Route::resource('appointments', AppointmentController::class);
        Route::post('appointments/{appointment}/assign', [\App\Http\Controllers\Booking\AppointmentController::class, 'assign'])->name('appointments.assign');
        Route::get('calendar/{date?}', [\App\Http\Controllers\Booking\AppointmentController::class, 'calendar'])->name('appointments.calendar');
        Route::resource('customers', CustomerController::class);
        Route::resource('services', ServiceController::class)->except(['show']);
        Route::resource('staff', StaffController::class);
        Route::get('staff/{staff}/schedule', [\App\Http\Controllers\Booking\StaffScheduleController::class, 'edit'])->name('staff.schedule');
        Route::put('staff/{staff}/schedule', [\App\Http\Controllers\Booking\StaffScheduleController::class, 'update'])->name('staff.schedule.update');
        Route::post('staff/{staff}/blocks', [\App\Http\Controllers\Booking\StaffScheduleController::class, 'storeBlock'])->name('staff.blocks.store');
        Route::delete('staff/{staff}/blocks/{block}', [\App\Http\Controllers\Booking\StaffScheduleController::class, 'destroyBlock'])->name('staff.blocks.destroy');
    });

    // POS module
    Route::get('/pos', [PosController::class, 'terminal'])->name('pos.terminal');
    Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

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

    // Suppliers
    Route::resource('suppliers', SupplierController::class);

    // Analytics
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // E-commerce — admin orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index')->middleware('module:ecommerce');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show')->middleware('module:ecommerce');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status')->middleware('module:ecommerce');

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

    // Admin — tenant management (only platform owner should access this; gate by role later)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
        Route::post('/tenants/{tenant}/module', [TenantController::class, 'toggleModule'])->name('tenants.module');
        Route::patch('/tenants/{tenant}/subdomain', [TenantController::class, 'updateSubdomain'])->name('tenants.subdomain');
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');

        // Billing sync queue
        Route::get('/sync-queue', [SyncQueueController::class, 'index'])->name('sync.index');
        Route::post('/sync-queue/{syncQueue}/retry', [SyncQueueController::class, 'retryOne'])->name('sync.retry');
        Route::post('/sync-queue/retry-all', [SyncQueueController::class, 'retryAll'])->name('sync.retry-all');
        Route::patch('/sync-queue/{syncQueue}/dismiss', [SyncQueueController::class, 'dismiss'])->name('sync.dismiss');

        // Logs
        Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
        Route::get('/logs/audit', [LogController::class, 'audit'])->name('logs.audit');
        Route::get('/logs/combined', [LogController::class, 'combined'])->name('logs.combined');
        Route::get('/logs/{log}', [LogController::class, 'show'])->name('logs.show');
        Route::patch('/logs/{log}/status', [LogController::class, 'updateStatus'])->name('logs.status');
    });

    // Settings — self-serve module management
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::post('/modules/request', [ModuleController::class, 'request'])->name('modules.request');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ─── Public client booking portal (/book/{slug}) ─────────────────────────────
// No staff auth required — customers create their own accounts here.
Route::prefix('book/{slug}')->name('book.')->group(function () {

    // Service listing
    Route::get('/',                           [PublicBookingController::class, 'index'])->name('index');
    Route::get('/services/{service}',         [PublicBookingController::class, 'service'])->name('service');
    Route::get('/slots',                      [PublicBookingController::class, 'slots'])->name('slots');
    Route::get('/confirm',                    [PublicBookingController::class, 'confirm'])->name('confirm');
    Route::post('/book',                      [PublicBookingController::class, 'store'])->name('store');
    Route::get('/success/{appointment}',      [PublicBookingController::class, 'success'])->name('success');

    // Customer auth
    Route::get('/login',                      [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',                     [CustomerAuthController::class, 'login'])->name('login.post');
    Route::get('/register',                   [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register',                  [CustomerAuthController::class, 'register'])->name('register.post');
    Route::post('/logout',                    [CustomerAuthController::class, 'logout'])->name('logout');

    // Customer portal (auth checked inside controller)
    Route::get('/my-bookings',                [CustomerPortalController::class, 'myBookings'])->name('my-bookings');
    Route::patch('/appointments/{appointment}/cancel', [CustomerPortalController::class, 'cancel'])->name('cancel');
});

// ─── Renter portal (/rent/{slug}) ────────────────────────────────────────────
Route::prefix('rent/{slug}')->name('rent.')->group(function () {
    Route::get('/login',            [RenterAuthController::class, 'showLogin'])->name('login');
    Route::post('/login',           [RenterAuthController::class, 'login'])->name('login.post');
    Route::post('/logout',          [RenterAuthController::class, 'logout'])->name('logout');

    // Portal pages (auth checked inside controller)
    Route::get('/',                 [RenterPortalController::class, 'portal'])->name('portal');
    Route::get('/lease',            [RenterPortalController::class, 'lease'])->name('lease');
    Route::get('/payments',         [RenterPortalController::class, 'payments'])->name('payments');
    Route::get('/maintenance',      [RenterPortalController::class, 'maintenance'])->name('maintenance');
    Route::post('/maintenance',     [RenterPortalController::class, 'submitMaintenance'])->name('maintenance.submit');
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

require __DIR__.'/auth.php';
