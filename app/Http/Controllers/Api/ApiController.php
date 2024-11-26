<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
  // User Register API (POST) - [name, email, password, role]    
  public function register(Request $request) 
  {
      // Form validation
      $request->validate([
          'name' => 'required|string',
          'email' => 'required|string|email|unique:users',
          'password' => 'required|confirmed', // password_confirmation
          'role' => 'sometimes|in:admin,customer'
      ]);

      // Create new user
      User::create([
          'name' => $request->name,
          'email' => $request->email,
          'password' => bcrypt($request->password),
          'role' => $request->role ?? 'customer',
          'active_token' => null
      ]);

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

      // Check if user exists
      $user = User::where('email', $request->email)->first();
      
      // If user exists and has active token, invalidate it
      if ($user && $user->active_token) {
          try {
              auth()->setToken($user->active_token)->invalidate();
              $user->active_token = null;
              $user->save();
          } catch (\Exception $e) {
              // Token might be already invalid
          }
      }

      // Attempt authentication and get new token
      $token = auth()->attempt([
          'email' => $request->email,
          'password' => $request->password
      ]);

      if ($token) {
          // Update user's active token
          $user = auth()->user();
          $user->active_token = $token;
          $user->save();

          return response()->json([
              'status' => true,
              'message' => $user->active_token ? 'Session switched to this device' : 'User logged in successfully',
              'token' => $token,
              'user' => $user
          ]);
      }

      return response()->json([
          'status' => false,
          'message' => 'Invalid credentials'
      ]);
  }

  // Get User Profile API (GET) - [Auth Token]
  public function profile() 
  {
      $user = Auth::user();

      return response()->json([
          'status' => true,
          'message' => 'User profile data',
          'data' => $user
      ]);
  }

  // Refresh Token API (GET) - [Auth Token]
  public function refreshToken() 
  {
      $user = auth()->user();
      $newToken = auth()->refresh();

      // Update active token
      $user->active_token = $newToken;
      $user->save();

      return response()->json([
          'status' => true,
          'message' => 'Token refreshed successfully',
          'token' => $newToken,
          'expires_in' => auth()->factory()->getTTL() * 60
      ]);
  }

  // User Logout API (GET) - [Auth Token]
  public function logout() 
  {
      // Clear active token
      $user = auth()->user();
      $user->active_token = null;
      $user->save();

      auth()->logout();

      return response()->json([
          'status' => true,
          'message' => 'User logged out successfully'
      ]);
  }
}