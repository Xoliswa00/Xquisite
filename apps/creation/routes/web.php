<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductItemController;
use App\Http\Controllers\ProductPriceController;
use App\Http\Controllers\ServiceComboController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ServiceCategoryController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\BillingController;



Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
Route::middleware(['auth'])->group(function () {
    Route::resource('customers', ClientController::class);
    Route::resource('invoices', InvoiceController::class);
    Route::resource('quotes', QuoteController::class);
    route::post('quotes/{quote}/send', [QuoteController::class, 'send'])->name('quotes.send');
        Route::resource('subscriptions', SubscriptionsController::class);
    Route::resource('payments', PaymentController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('clients', ClientController::class);
    Route::get('profile', [ClientController::class, 'profile'])->name('clients.profile');
    Route::put('profile', [ClientController::class, 'updateProfile'])->name('clients.update');

    Route::resource('products', ProductController::class);
    // Items
Route::post('products/{product}/items', [ProductItemController::class, 'store']);
Route::delete('items/{item}', [ProductItemController::class, 'destroy']);

// Prices
Route::post('products/{product}/prices', [ProductPriceController::class, 'store']);

    // Service Combos
    Route::resource('combos', ServiceComboController::class);
    Route::post('combos/{combo}/toggle', [ServiceComboController::class, 'toggle'])->name('combos.toggle');

    // Promotions
    Route::resource('promotions', PromotionController::class);
    Route::post('promotions/{promotion}/toggle', [PromotionController::class, 'toggle'])->name('promotions.toggle');

    // Service Categories
    Route::resource('service-categories', ServiceCategoryController::class);
    Route::get('api/service-categories', [ServiceCategoryController::class, 'apiList'])->name('service-categories.api');

    // Booking menu
    Route::get('booking', [BookingController::class, 'menu'])->name('booking.menu');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Owner ↔ Client messaging (owner side)
    Route::get('clients/{client}/messages', [CommunicationController::class, 'thread'])->name('communications.thread');
    Route::post('clients/{client}/messages', [CommunicationController::class, 'store'])->name('communications.store');

    // Client portal
    Route::get('portal/dashboard', [ClientPortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::get('portal/messages', [CommunicationController::class, 'clientIndex'])->name('portal.messages');
    Route::post('portal/messages/reply', [CommunicationController::class, 'clientReply'])->name('portal.messages.reply');

    // Platform owner billing (own subscription)
    Route::get('billing', [BillingController::class, 'index'])->name('billing.index');
    Route::get('billing/{invoice}', [BillingController::class, 'show'])->name('billing.show');

    // System admin billing management
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('billing', [BillingController::class, 'adminIndex'])->name('billing.index');
        Route::get('billing/{company}', [BillingController::class, 'adminShow'])->name('billing.show');
        Route::post('billing/{company}/generate', [BillingController::class, 'adminGenerateInvoice'])->name('billing.generate');
        Route::post('billing/{company}/suspend', [BillingController::class, 'adminSuspend'])->name('billing.suspend');
        Route::post('billing/{company}/reactivate', [BillingController::class, 'adminReactivate'])->name('billing.reactivate');
        Route::post('billing/invoices/{invoice}/mark-paid', [BillingController::class, 'adminMarkPaid'])->name('billing.mark-paid');
    });
});
Route::get('/client-portal/accept/{token}', [ClientController::class, 'accept'])->name('client.invitation.accept')->middleware('signed');