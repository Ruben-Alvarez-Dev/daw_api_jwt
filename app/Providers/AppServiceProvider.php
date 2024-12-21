<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider
 * 
 * Registers application-wide services and configurations:
 * - Database connections
 * - Third-party services
 * - Global configurations
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services
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
        //
    }
}
