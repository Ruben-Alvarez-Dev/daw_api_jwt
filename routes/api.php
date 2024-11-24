<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

// Open Routes
// User Register API
Route::post("register", [ApiController::class, "register"]);
// User Login API
Route::post("login", [ApiController::class, "login"]);

// Protected Routes
Route::group([
    "middleware" => ["auth:api"]
], function(){
    // User Profile API
    Route::get("profile", [ApiController::class, "profile"]);
    // Refresh Token API
    Route::get("refresh-token", [ApiController::class, "refreshToken"]);
    // User Logout API
    Route::get("logout", [ApiController::class, "logout"]);
});