<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\ReservationController;

// Open Routes
Route::post("register", [ApiController::class, "register"]);
Route::post("login", [ApiController::class, "login"]);

// Protected Routes
Route::group([
    "middleware" => ["auth:api"]
], function(){
    // Ruta de validación de token (nueva)
    Route::post('/validate-token', function (Request $request) {
        return response()->json(['valid' => true]);
    });

    // User Profile Routes
    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh-token", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);

    // Admin Routes
    Route::group(['middleware' => ['check.role:admin']], function () {
        // User management (solo admin)
        Route::get('users', [ApiController::class, 'getUsers']);
        Route::post('users', [ApiController::class, 'store']);
        Route::put('users/{user}', [ApiController::class, 'update']);
        Route::delete('users/{user}', [ApiController::class, 'destroy']);
        
        // Restaurant management (solo POST, PUT, DELETE)
        Route::post('restaurants', [RestaurantController::class, 'store']);
        Route::put('restaurants/{restaurant}', [RestaurantController::class, 'update']);
        Route::delete('restaurants/{restaurant}', [RestaurantController::class, 'destroy']);
        
        // Table management (solo POST, PUT, DELETE)
        Route::post('tables', [TableController::class, 'store']);
        Route::put('tables/{table}', [TableController::class, 'update']);
        Route::delete('tables/{table}', [TableController::class, 'destroy']);
    });

    // Public Routes (requieren auth pero no admin)
    // Restaurants (solo GET)
    Route::get('restaurants', [RestaurantController::class, 'index']);
    Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show']);
    
    // Tables (solo GET)
    Route::get('tables', [TableController::class, 'index']);
    Route::get('tables/{table}', [TableController::class, 'show']);
    
    // Reservations (todas las operaciones)
    Route::apiResource('reservations', ReservationController::class);

    // Additional Routes
    Route::get('restaurants/{id_restaurant}/tables', [TableController::class, 'getTablesByRestaurant']);
    Route::get('restaurants/{id_restaurant}/reservations', [ReservationController::class, 'getReservationsByRestaurant']);
    Route::get('my-reservations', [ReservationController::class, 'getUserReservations']);
});