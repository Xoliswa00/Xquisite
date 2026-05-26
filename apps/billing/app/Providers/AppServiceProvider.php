<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Client;
use App\Models\Company;
use App\Models\CompanyDomain;
use App\Models\CompanyInvitation;
use App\Models\CompanyUser;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\ProductItem;
use App\Models\ProductPrice;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Subscription;

use App\Policies\ClientPolicy;
use App\Policies\CompanyDomainsPolicy;
use App\Policies\CompanyInvitationsPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\CompanyUserPolicy;
use App\Policies\InvoiceItemsPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use App\Policies\ProductCategoryPolicy;
use App\Policies\ProductGroupPolicy;
use App\Policies\ProductItemsPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ProductPricePolicy;
use App\Policies\QuoteItemsPolicy;
use App\Policies\QuotePolicy;
use App\Policies\SubscriptionsPolicy;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Client::class,             ClientPolicy::class);
        Gate::policy(Company::class,            CompanyPolicy::class);
        Gate::policy(CompanyDomain::class,      CompanyDomainsPolicy::class);
        Gate::policy(CompanyInvitation::class,  CompanyInvitationsPolicy::class);
        Gate::policy(CompanyUser::class,        CompanyUserPolicy::class);
        Gate::policy(Invoice::class,            InvoicePolicy::class);
        Gate::policy(InvoiceItem::class,        InvoiceItemsPolicy::class);
        Gate::policy(Payment::class,            PaymentPolicy::class);
        Gate::policy(Product::class,            ProductPolicy::class);
        Gate::policy(ProductCategory::class,    ProductCategoryPolicy::class);
        Gate::policy(ProductGroup::class,       ProductGroupPolicy::class);
        Gate::policy(ProductItem::class,        ProductItemsPolicy::class);
        Gate::policy(ProductPrice::class,       ProductPricePolicy::class);
        Gate::policy(Quote::class,              QuotePolicy::class);
        Gate::policy(QuoteItem::class,          QuoteItemsPolicy::class);
        Gate::policy(Subscription::class,       SubscriptionsPolicy::class);
    }
}
