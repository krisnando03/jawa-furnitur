<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View; // Import View facade
use App\Http\View\Composers\CartComposer; // Import CartComposer
use App\Http\View\Composers\OrderComposer; // Import OrderComposer
use App\Http\View\Composers\NavbarDataComposer; // Import NavbarDataComposer
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || str_contains(config('app.url'), 'ngrok-free.app')) {
            \URL::forceScheme('https');
        }
        Paginator::useBootstrapFive();
        View::composer('frontend.layouts.app', CartComposer::class); // Daftarkan composer untuk view yang sesuai
        View::composer('frontend.layouts.app', OrderComposer::class); // Daftarkan composer untuk view yang sesuai
        View::composer('frontend.layouts.app', NavbarDataComposer::class);
    }
}
