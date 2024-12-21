<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Auth;

/**
 * Authentication and User Management API
 * 
 * @group Authentication
 */
class ApiController extends Controller
{
    /**
     * Verifica si el usuario tiene acceso a gestionar otro usuario
     */
    private function checkUserAccess(User $targetUser)
    {
        $user = auth()->user();
        
        // Admin tiene acceso a todos los usuarios
        if ($user->user_role === 'admin') {
            return true;
        }
        
        // Supervisor solo tiene acceso a usuarios que haya creado
        if ($user->user_role === 'supervisor') {
            return $targetUser->user_created_by === $user->user_email;
        }
        
        // Customer solo tiene acceso a su propio perfil
        return $user->user_email === $targetUser->user_email;
    }

    /**
     * Register new user
     * - Admin puede crear cualquier tipo de usuario
     * - Supervisor solo puede crear customers
     * - Público puede registrarse como customer
     * 
     * @authenticated false
     * @bodyParam user_name string required User's full name
     * @bodyParam user_email string required Unique email address
     * @bodyParam user_password string required Min 6 characters
     * @bodyParam user_password_confirmation string required Must match password
     * @bodyParam user_role string optional Enum: admin, supervisor, customer. Default: customer
     */
    public function register(Request $request) 
    {
        // Form validation
        $request->validate([
            'user_name' => 'required|string',
            'user_email' => 'required|string|email|unique:users,user_email',
            'user_password' => 'required|confirmed', // password_confirmation
            'user_role' => 'sometimes|in:admin,supervisor,customer'
        ]);

        // Si no está autenticado, solo puede registrarse como customer
        if (!auth()->check()) {
            if ($request->has('user_role') && $request->user_role !== 'customer') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized role selection'
                ], 403);
            }
            $role = 'customer';
            $createdBy = null;
        } 
        // Si está autenticado, verificar permisos
        else {
            $user = auth()->user();
            
            // Supervisor solo puede crear customers
            if ($user->user_role === 'supervisor') {
                if ($request->has('user_role') && $request->user_role !== 'customer') {
                    return response()->json([
                        'status' => false,
                        'message' => 'Supervisors can only create customer accounts'
                    ], 403);
                }
                $role = 'customer';
                $createdBy = $user->user_email;
            }
            // Admin puede crear cualquier tipo de usuario
            elseif ($user->user_role === 'admin') {
                $role = $request->user_role ?? 'customer';
                $createdBy = $user->user_email;
            }
            // Customer no puede crear usuarios
            else {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized to create users'
                ], 403);
            }
        }

        // Create new user
        User::create([
            'user_name' => $request->user_name,
            'user_email' => $request->user_email,
            'user_password' => bcrypt($request->user_password),
            'user_role' => $role,
            'user_created_by' => $createdBy,
            'active_token' => null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User registered successfully'
        ]);
    }

    /**
     * User login
     * 
     * @authenticated false
     */
    public function login(Request $request) 
    {
        $request->validate([
            'user_email' => 'required|email',
            'user_password' => 'required'
        ]);

        $credentials = [
            'user_email' => $request->user_email,
            'user_password' => $request->user_password
        ];

        if ($token = auth()->attempt($credentials)) {
            $user = auth()->user();
            $user->active_token = $token;
            $user->save();

            return response()->json([
                'status' => true,
                'message' => $user->active_token ? 'Session switched to this device' : 'User logged in successfully',
                'token' => $token,
                'user' => [
                    'user_id' => $user->user_id,
                    'user_name' => $user->user_name,
                    'user_email' => $user->user_email,
                    'user_role' => $user->user_role
                ]
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    /**
     * Get authenticated user's profile
     * Cada usuario puede ver su propio perfil
     * 
     * @authenticated
     */
    public function profile() 
    {
        return response()->json([
            'status' => true,
            'message' => 'User profile data',
            'data' => [
                'user_id' => auth()->user()->user_id,
                'user_name' => auth()->user()->user_name,
                'user_email' => auth()->user()->user_email,
                'user_role' => auth()->user()->user_role
            ]
        ]);
    }

    /**
     * Get users based on role
     * - Admin: todos los usuarios
     * - Supervisor: usuarios que ha creado
     * - Customer: no autorizado
     * 
     * @authenticated
     */
    public function getUsers()
    {
        $user = auth()->user();

        if ($user->user_role === 'customer') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = User::query();

        // Supervisor solo ve los usuarios que ha creado
        if ($user->user_role === 'supervisor') {
            $query->where('user_created_by', $user->user_email);
        }

        $users = $query->get();
        $data = [];
        
        foreach ($users as $user) {
            $data[] = [
                'user_id' => $user->user_id,
                'user_name' => $user->user_name,
                'user_email' => $user->user_email,
                'user_role' => $user->user_role,
                'user_created_by' => $user->user_created_by
            ];
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    /**
     * Refresh JWT Token
     * 
     * @authenticated
     */
    public function refreshToken() 
    {
        try {
            $newToken = auth()->refresh();
            
            $user = auth()->user();
            $user->active_token = $newToken;
            $user->save();

            return response()->json([
                'status' => true,
                'token' => $newToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Could not refresh token'
            ], 401);
        }
    }

    /**
     * User logout
     * 
     * @authenticated
     */
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
     * - Admin puede actualizar cualquier usuario
     * - Supervisor puede actualizar usuarios que haya creado
     * - Customer solo puede actualizar su perfil
     * 
     * @authenticated
     */
    public function update(Request $request, User $user)
    {
        if (!$this->checkUserAccess($user)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $authUser = auth()->user();
        
        // Validación básica
        $rules = [
            'user_name' => 'sometimes|string',
            'user_email' => 'sometimes|string|email|unique:users,user_email,' . $user->user_id . ',user_id',
            'user_password' => 'sometimes|confirmed'
        ];

        // Solo admin puede cambiar roles
        if ($authUser->user_role === 'admin') {
            $rules['user_role'] = 'sometimes|in:admin,supervisor,customer';
        }
        // Supervisor solo puede gestionar customers
        elseif ($authUser->user_role === 'supervisor') {
            if ($request->has('user_role') && $request->user_role !== 'customer') {
                return response()->json([
                    'status' => false,
                    'message' => 'Supervisors can only manage customer accounts'
                ], 403);
            }
        }

        $request->validate($rules);

        // Preparar datos para actualizar
        $data = $request->only(['user_name', 'user_email']);
        
        // Solo admin puede cambiar roles
        if ($authUser->user_role === 'admin' && $request->has('user_role')) {
            $data['user_role'] = $request->user_role;
        }
        
        // Actualizar contraseña si se proporciona
        if ($request->has('user_password')) {
            $data['user_password'] = bcrypt($request->user_password);
        }

        $user->update($data);

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => [
                'user_id' => $user->user_id,
                'user_name' => $user->user_name,
                'user_email' => $user->user_email,
                'user_role' => $user->user_role,
                'user_created_by' => $user->user_created_by
            ]
        ]);
    }

    /**
     * Delete user
     * - Admin puede eliminar cualquier usuario
     * - Supervisor puede eliminar usuarios que haya creado
     * - Customer no puede eliminar usuarios
     * 
     * @authenticated
     */
    public function destroy(User $user)
    {
        if (!$this->checkUserAccess($user)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // No permitir auto-eliminación
        if ($user->user_email === auth()->user()->user_email) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete your own account'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}