<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;

/**
 * Authentication Service Provider
 * 
 * Registers authentication-related services:
 * - Policy mappings for authorization
 * - Custom authentication providers
 * - JWT authentication configuration
 * 
 * This provider extends Laravel's base authentication
 * to support custom field names and JWT tokens.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Model to policy mappings
     * 
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register authentication services
     * 
     * Configures:
     * - Custom user provider for JWT
     * - JWT authentication guard
     */
    public function boot(): void
    {
        Auth::provider('custom', function ($app, array $config) {
            return new CustomUserProvider(
                $app['hash'],
                $config['model']
            );
        });
    }
}
