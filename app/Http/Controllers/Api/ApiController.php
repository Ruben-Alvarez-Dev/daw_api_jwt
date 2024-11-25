<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
   // User Register API (POST) - [name, email, password]    
   public function register(Request $request) 
   {
       // Form validation
       $request->validate([
           'name' => 'required|string',
           'email' => 'required|string|email|unique:users',
           'password' => 'required|confirmed', // password_confirmation
           'role' => 'sometimes|in:admin,customer'
       ]);

       // User model class to save data
       User::create([
           'name' => $request->name,
           'email' => $request->email,
           'password' => bcrypt($request->password),
           'role' => $request->role ?? 'customer'
       ]);

       // Response
       return response()->json([
           'status' => true,
           'message' => 'User registered successfully'
       ]);
   }

   // User Login API (POST) - [email, password]
   public function login(Request $request) 
   {
       // Form validation
       $request->validate([
           'email' => 'required|email',
           'password' => 'required'
       ]);

       // User check on the basis of email and password
       $token = auth()->attempt([
           'email' => $request->email,
           'password' => $request->password
       ]);

       // JWT Token - Auth Token
       if($token){
           return response()->json([
               'status' => true,
               'message' => 'User logged in',
               'token' => $token,
               'user' => auth()->user()  // Añadido para ver el rol del usuario
           ]);
       }

       // Response
       return response()->json([
           'status' => false,
           'message' => 'Invalid credentials'
       ]);
   }
   
   // User Profile API (GET) - [Auth Token]
   public function profile() 
   {
       // $userdata = auth() -> user();
       // $userdata = request() -> user();
       $userdata = Auth::user();

       return response() -> json([
           'status' => true,
           'message' => 'User profile data',
           'data' => $userdata,
           'id' => auth() -> user() -> id,
           'email' => request() -> user() -> email
       ]);
   }

   // Refresh Token API (GET) - [Auth Token]
   public function refreshToken() 
   {
       $newToken = auth() -> refresh();

       return response() -> json([
           'status' => true,
           'message' => 'Token refreshed',
           'token' => $newToken,
           'expires_in' => auth() -> factory() -> getTTL() * 60
       ]);
   }

   // User Logout API (GET) - [Auth Token]
   public function logout() 
   {
       auth() -> logout();

       return response() -> json([
           'status' => true,
           'message' => 'User logged out'
       ]);
   }
}