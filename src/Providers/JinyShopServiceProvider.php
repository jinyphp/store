<?php

namespace Jiny\Shop\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class JinyShopServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load package views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'jiny-shop');

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../../databases/migrations');

        // Load package routes
        $this->loadRoutes();

        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/shop.php' => config_path('shop.php'),
            ], 'jiny-shop-config');

            // Publish views
            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/jiny-shop'),
            ], 'jiny-shop-views');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../../databases/migrations' => database_path('migrations'),
            ], 'jiny-shop-migrations');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/shop.php', 'shop');
    }

    /**
     * Load package routes
     */
    protected function loadRoutes(): void
    {
        // Load admin routes with admin middleware
        Route::middleware(['web', 'admin'])
            ->group(__DIR__.'/../../routes/admin.php');

        // Load web routes
        Route::middleware(['web'])
            ->group(__DIR__.'/../../routes/web.php');
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }
}