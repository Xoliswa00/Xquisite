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
use App\Http\Controllers\Settings\ModuleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Booking module
    Route::resource('appointments', AppointmentController::class);
    Route::resource('customers', CustomerController::class);
    Route::resource('services', ServiceController::class)->except(['show']);
    Route::resource('staff', StaffController::class);

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

    // Module-gated route groups
    Route::middleware('module:booking')->group(function () {
        // Booking routes already defined above — middleware applied at group level
        // (resource routes are defined outside; gate them by wrapping in this group if needed later)
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
