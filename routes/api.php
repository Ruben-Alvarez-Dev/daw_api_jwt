<?php

/**
 * API Routes
 * 
 * Public routes:
 * - POST /register: User registration
 * - POST /login: User authentication
 * 
 * Protected routes (require JWT token):
 * - GET /profile: Get user profile
 * - POST /logout: End user session
 * 
 * Admin routes (require admin role):
 * - Restaurant management
 * - Table management
 * - User management
 * 
 * Customer routes:
 * - Reservation management
 * - Restaurant viewing
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ReservationController;

// Rutas públicas
Route::post("register", [ApiController::class, "register"]);
Route::post("login", [ApiController::class, "login"]);

// Rutas protegidas (requieren autenticación)
Route::group(["middleware" => ["auth:api"]], function(){
    // Validación de token
    Route::post('/validate-token', function (Request $request) {
        return response()->json(['valid' => true]);
    });

    // Perfil de usuario
    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh-token", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);

    // Rutas para Admin
    Route::group(['middleware' => 'check.role:admin'], function () {
        // Gestión de usuarios
        Route::get('users', [ApiController::class, 'getUsers']);
        Route::post('users', [ApiController::class, 'store']);
        Route::put('users/{user}', [ApiController::class, 'update']);
        Route::delete('users/{user}', [ApiController::class, 'destroy']);
        
        // Gestión completa de restaurantes
        Route::post('restaurants', [RestaurantController::class, 'store']);
        Route::put('restaurants/{restaurant}', [RestaurantController::class, 'update']);
        Route::delete('restaurants/{restaurant}', [RestaurantController::class, 'destroy']);
    });

    // Rutas para Supervisores y Admin
    Route::middleware('check.role:admin:supervisor')->group(function () {
        // Gestión de mesas (el controlador verifica que el supervisor solo acceda a su restaurante)
        Route::get('tables', [TableController::class, 'index']);
        Route::post('tables', [TableController::class, 'store']);
        Route::get('tables/{table}', [TableController::class, 'show']);
        Route::put('tables/{table}', [TableController::class, 'update']);
        Route::delete('tables/{table}', [TableController::class, 'destroy']);
        Route::get('restaurants/{id_restaurant}/tables', [TableController::class, 'getTablesByRestaurant']);
    });

    // Rutas accesibles para todos los usuarios autenticados
    // Ver restaurantes (customers, supervisors, admin)
    Route::get('restaurants', [RestaurantController::class, 'index']);
    Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show']);
    
    // Gestión de reservas
    Route::apiResource('reservations', ReservationController::class);
    Route::get('restaurants/{id_restaurant}/reservations', [ReservationController::class, 'getReservationsByRestaurant']);
    Route::get('my-reservations', [ReservationController::class, 'getUserReservations']);
});