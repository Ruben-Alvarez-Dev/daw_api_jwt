<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

/**
 * Restaurant Management API
 * 
 * @group Restaurants
 */
class RestaurantController extends Controller
{
    /**
     * Verifica si el usuario tiene acceso al restaurante
     */
    private function checkRestaurantAccess($restaurant_id)
    {
        $user = auth()->user();
        
        // Admin tiene acceso a todo
        if ($user->user_role === 'admin') {
            return true;
        }
        
        // Supervisor solo tiene acceso a su restaurante
        if ($user->user_role === 'supervisor') {
            $restaurant = Restaurant::find($restaurant_id);
            return $restaurant && $restaurant->restaurant_supervisor_email === $user->user_email;
        }
        
        return false;
    }

    /**
     * List all restaurants
     * Todos los usuarios pueden ver la lista de restaurantes
     * 
     * @authenticated
     * @response 200 {
     *   "status": true,
     *   "data": [
     *     {
     *       "restaurant_id": 1,
     *       "restaurant_name": "Restaurant Name",
     *       "restaurant_food_type": "Italian",
     *       "restaurant_status": "active"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $restaurants = Restaurant::all();
        return response()->json([
            'status' => true,
            'data' => $restaurants
        ]);
    }

    /**
     * Create new restaurant
     * Solo el admin puede crear restaurantes
     * 
     * @authenticated
     * @role admin
     * 
     * @bodyParam restaurant_name string required Restaurant display name
     * @bodyParam restaurant_business_name string required Legal business name
     * @bodyParam restaurant_food_type string required Type of cuisine
     * @bodyParam restaurant_capacity integer required Total capacity
     * @bodyParam restaurant_business_email string required unique Business contact
     * @bodyParam restaurant_supervisor_email string required Manager email
     * @bodyParam restaurant_phone string required Contact number
     * @bodyParam restaurant_description string required Full description
     * @bodyParam restaurant_zones json required Array of dining zones
     * 
     * @response 201 {
     *   "status": true,
     *   "message": "Restaurant created successfully",
     *   "data": {
     *     "restaurant_id": 1,
     *     "restaurant_name": "Restaurant Name"
     *   }
     * }
     */
    public function store(Request $request)
    {
        if (auth()->user()->user_role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $request->validate([
            'restaurant_name' => 'required|string|max:255',
            'restaurant_business_name' => 'required|string|max:255',
            'restaurant_food_type' => 'required|string|max:100',
            'restaurant_capacity' => 'required|integer|min:1',
            'restaurant_business_email' => 'required|email|unique:restaurants,restaurant_business_email',
            'restaurant_supervisor_email' => 'required|email',
            'restaurant_phone' => 'required|string|max:20',
            'restaurant_description' => 'required|string',
            'restaurant_zones' => ['required', function ($attribute, $value, $fail) {
                if (!is_array($value) && !is_string($value)) {
                    $fail('The restaurant zones must be an array or a JSON string.');
                    return;
                }
                if (is_string($value) && !json_decode($value)) {
                    $fail('The restaurant zones must be a valid JSON string.');
                }
            }]
        ]);

        $data = $request->all();
        if (is_array($data['restaurant_zones'])) {
            $data['restaurant_zones'] = json_encode($data['restaurant_zones']);
        }

        $restaurant = Restaurant::create($data);
        return response()->json([
            'status' => true,
            'message' => 'Restaurant created successfully',
            'data' => $restaurant
        ], 201);
    }

    /**
     * Display restaurant details
     * Todos los usuarios pueden ver los detalles de un restaurante
     * 
     * @authenticated
     */
    public function show($restaurant_id)
    {
        $restaurant = Restaurant::find($restaurant_id);
        
        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $restaurant
        ]);
    }

    /**
     * Update restaurant
     * Admin puede actualizar cualquier restaurante
     * Supervisor puede actualizar solo su restaurante
     * 
     * @authenticated
     */
    public function update(Request $request, $restaurant_id)
    {
        if (!$this->checkRestaurantAccess($restaurant_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $restaurant = Restaurant::find($restaurant_id);
        
        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }

        $request->validate([
            'restaurant_name' => 'sometimes|string|max:255',
            'restaurant_business_name' => 'sometimes|string|max:255',
            'restaurant_food_type' => 'sometimes|string|max:100',
            'restaurant_capacity' => 'sometimes|integer|min:1',
            'restaurant_business_email' => 'sometimes|email|unique:restaurants,restaurant_business_email,'.$restaurant_id.',restaurant_id',
            'restaurant_phone' => 'sometimes|string|max:20',
            'restaurant_description' => 'sometimes|string',
            'restaurant_zones' => ['sometimes', function ($attribute, $value, $fail) {
                if (!is_array($value) && !is_string($value)) {
                    $fail('The restaurant zones must be an array or a JSON string.');
                    return;
                }
                if (is_string($value) && !json_decode($value)) {
                    $fail('The restaurant zones must be a valid JSON string.');
                }
            }],
            'restaurant_status' => 'sometimes|in:active,inactive,tables available,fully booked'
        ]);

        // Si es supervisor, no puede cambiar el supervisor_email
        if (auth()->user()->user_role === 'supervisor' && $request->has('restaurant_supervisor_email')) {
            return response()->json([
                'status' => false,
                'message' => 'Supervisor cannot change restaurant_supervisor_email'
            ], 403);
        }

        $data = $request->all();
        if (is_array($data['restaurant_zones'])) {
            $data['restaurant_zones'] = json_encode($data['restaurant_zones']);
        }

        $restaurant->update($data);
        return response()->json([
            'status' => true,
            'message' => 'Restaurant updated successfully',
            'data' => $restaurant
        ]);
    }

    /**
     * Delete restaurant
     * Solo el admin puede eliminar restaurantes
     * 
     * @authenticated
     * @role admin
     */
    public function destroy($restaurant_id)
    {
        if (auth()->user()->user_role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $restaurant = Restaurant::find($restaurant_id);
        
        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }

        $restaurant->delete();
        return response()->json([
            'status' => true,
            'message' => 'Restaurant deleted successfully'
        ]);
    }
}