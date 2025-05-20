<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/';

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->routes(function () {
            Route::middleware('api')
                ->group(base_path(path: 'routes/api.php'))
            ;
        });
    }

    ///**
    // * Register services.
    // */
    //public function register(): void
    //{
    //    //
    //}
}
