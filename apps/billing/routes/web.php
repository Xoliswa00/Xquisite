<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyUserController;
use App\Http\Controllers\CompanyInvitationsController;
use App\Http\Controllers\CompanyDomainsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductItemsController;
use App\Http\Controllers\ProductPriceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LogController;

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

    // Clients
    Route::resource('clients', ClientController::class);
    Route::get('profile', [ClientController::class, 'profile'])->name('clients.profile');
    Route::put('profile', [ClientController::class, 'updateProfile'])->name('clients.update');

    // Invoices
    Route::resource('invoices', InvoiceController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy']);
    Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    // Quotes
    Route::resource('quotes', QuoteController::class);
    Route::post('quotes/{quote}/send',    [QuoteController::class, 'send'])->name('quotes.send');
    Route::post('quotes/{quote}/submit',  [QuoteController::class, 'submit'])->name('quotes.submit');
    Route::post('quotes/{quote}/approve', [QuoteController::class, 'approve'])->name('quotes.approve');
    Route::post('quotes/{quote}/reject',  [QuoteController::class, 'reject'])->name('quotes.reject');
    Route::post('quotes/{quote}/convert', [InvoiceController::class, 'createFromQuote'])->name('quotes.convert');
    Route::get('quotes/{quote}/download', [QuoteController::class, 'download'])->name('quotes.download');

    // Payments
    Route::resource('payments', PaymentController::class)->only(['index', 'show', 'destroy']);

    // Products
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/items', [ProductItemsController::class, 'store'])->name('products.items.store');
    Route::put('products/{product}/items/{item}', [ProductItemsController::class, 'update'])->name('products.items.update');
    Route::delete('products/{product}/items/{item}', [ProductItemsController::class, 'destroy'])->name('products.items.destroy');
    Route::post('products/{product}/prices', [ProductPriceController::class, 'store'])->name('products.prices.store');

    // Companies
    Route::resource('companies', CompanyController::class);
    Route::post('companies/{company}/switch', [CompanyController::class, 'switch'])->name('companies.switch');
    Route::post('companies/{company}/users', [CompanyUserController::class, 'store'])->name('companies.users.store');
    Route::put('companies/{company}/users/{user}/role', [CompanyUserController::class, 'updateRole'])->name('companies.users.role');
    Route::delete('companies/{company}/users/{user}', [CompanyUserController::class, 'destroy'])->name('companies.users.destroy');
    Route::get('companies/{company}/users', [CompanyUserController::class, 'index'])->name('companies.users.index');
    Route::post('companies/{company}/invitations', [CompanyInvitationsController::class, 'store'])->name('companies.invitations.store');
    Route::get('invitations/accept/{token}', [CompanyInvitationsController::class, 'accept'])->name('invitations.accept');
    Route::post('companies/{company}/domains', [CompanyDomainsController::class, 'store'])->name('companies.domains.store');
    Route::delete('companies/{company}/domains/{companyDomain}', [CompanyDomainsController::class, 'destroy'])->name('companies.domains.destroy');

    // Subscriptions
    Route::resource('subscriptions', SubscriptionsController::class);

    // Logs
    Route::get('logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('logs/audit', [LogController::class, 'audit'])->name('logs.audit');
    Route::get('logs/{log}', [LogController::class, 'show'])->name('logs.show');
    Route::patch('logs/{log}/status', [LogController::class, 'updateStatus'])->name('logs.status');

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');
});

Route::get('/client-portal/accept/{token}', [ClientController::class, 'accept'])
    ->name('client.invitation.accept')
    ->middleware('signed');
