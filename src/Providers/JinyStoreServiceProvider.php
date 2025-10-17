<?php

namespace Jiny\Store\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class JinyStoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load package views
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'jiny-store');

        // Load package migrations
        $this->loadMigrationsFrom(__DIR__.'/../../databases/migrations');

        // Load package routes
        $this->loadRoutes();

        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/store.php' => config_path('store.php'),
            ], 'jiny-store-config');

            // Publish views
            $this->publishes([
                __DIR__.'/../../resources/views' => resource_path('views/vendor/jiny-store'),
            ], 'jiny-store-views');

            // Publish migrations
            $this->publishes([
                __DIR__.'/../../databases/migrations' => database_path('migrations'),
            ], 'jiny-store-migrations');
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__.'/../../config/store.php', 'store');
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