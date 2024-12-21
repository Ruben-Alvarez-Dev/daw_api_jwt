<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * Route Service Provider
 * 
 * Configures routing for the application:
 * - API routes with prefix 'api'
 * - CORS middleware for API routes
 * - Rate limiting for API endpoints
 * - Route model bindings
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * API Routes Configuration
     * 
     * The path where API routes are defined
     */
    public const HOME = '/home';

    /**
     * Configure route model bindings, pattern filters, etc.
     * 
     * Sets up:
     * - API rate limiting
     * - Route prefixes and middleware
     * - Default route patterns
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
