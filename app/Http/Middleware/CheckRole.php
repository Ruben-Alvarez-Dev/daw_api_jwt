<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Role-based Access Control Middleware
 * 
 * Verifies if the authenticated user has the required role
 * to access a specific route or resource.
 * 
 * Usage in routes:
 * Route::middleware('check.role:admin')->group(function () {
 *     // Routes that require admin role
 * });
 * 
 * Multiple roles:
 * Route::middleware('check.role:admin:supervisor')->group(function () {
 *     // Routes that require admin OR supervisor role
 * });
 * 
 * @throws AuthorizationException if user lacks required role
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string $roles)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'No autorizado'], 401);
        }

        $allowedRoles = explode(':', $roles);
        $userRole = auth()->user()->user_role;
        
        dump([
            'allowedRoles' => $allowedRoles,
            'userRole' => $userRole,
            'user' => auth()->user()->toArray(),
            'request_path' => $request->path(),
            'request_method' => $request->method()
        ]);

        if (!in_array($userRole, $allowedRoles)) {
            return response()->json(['error' => 'No tienes permisos'], 403);
        }

        return $next($request);
    }
}