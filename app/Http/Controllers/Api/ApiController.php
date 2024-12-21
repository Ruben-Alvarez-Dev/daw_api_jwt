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
            'user_name' => 'required|string',
            'user_email' => 'required|string|email|unique:users,user_email',
            'user_password' => 'required|confirmed', // password_confirmation
            'user_role' => 'sometimes|in:admin,customer'
        ]);

        // Create new user
        User::create([
            'user_name' => $request->user_name,
            'user_email' => $request->user_email,
            'user_password' => bcrypt($request->user_password),
            'user_role' => $request->user_role ?? 'customer',
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
            'user_email' => 'required|email',
            'user_password' => 'required'
        ]);

        $credentials = [
            'email' => $request->user_email,
            'password' => $request->user_password
        ];

        if ($token = auth()->attempt($credentials)) {
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
        ], 401);
    }

    // Get User Profile API (GET) - [Auth Token]
    public function profile() 
    {
        return response()->json([
            'status' => true,
            'message' => 'User profile data',
            'data' => auth()->user()
        ]);
    }

    // Get All Users API (GET) - [Auth Token + Admin Role]
    public function getUsers()
    {
        if (auth()->user()->user_role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data' => User::all()
        ]);
    }

    // Refresh Token API (GET) - [Auth Token]
    public function refreshToken() 
    {
        $newToken = auth()->refresh();
        
        $user = auth()->user();
        $user->active_token = $newToken;
        $user->save();

        return response()->json([
            'status' => true,
            'token' => $newToken
        ]);
    }

    // User Logout API (GET) - [Auth Token]
    public function logout() 
    {
        $user = auth()->user();
        $user->active_token = null;
        $user->save();
        
        auth()->logout();
        
        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Update user information
     * 
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        // Validate request data
        $request->validate([
            'user_name' => 'sometimes|string',
            'user_email' => 'sometimes|string|email|unique:users,user_email,' . $user->id,
            'user_password' => 'sometimes|confirmed',
            'user_role' => 'sometimes|in:admin,customer,supervisor'
        ]);

        // Prepare data for update
        $data = $request->only(['user_name', 'user_email', 'user_role']);
        
        // Only hash password if it's provided
        if ($request->has('user_password')) {
            $data['user_password'] = bcrypt($request->user_password);
        }

        // Update user
        $user->update($data);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => $user
        ]);
    }
}