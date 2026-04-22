<?php

namespace App\Providers\Modules\Booking;

use Illuminate\Support\ServiceProvider;
use App\Modules\Booking\Support\ObserverRegistrar;


class BookingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
   public function boot(): void
    {
        ObserverRegistrar::register(
            app_path('Modules/Booking'),
            'App\\Modules\\Booking'
        );
    }
}
