<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        // Todas las listas con paginas usan nuestra vista en espanol
        // (la de fabrica de Laravel sale en ingles).
        Paginator::defaultView('vendor.pagination.llantas');
    }
}
