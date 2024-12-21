<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Http\Request;

/**
 * Restaurant Tables API
 * 
 * @group Tables
 */
class TableController extends Controller
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
            \Log::info('Comparing emails', [
                'user_email' => $user->user_email,
                'supervisor_email' => $restaurant ? $restaurant->restaurant_supervisor_email : null
            ]);
            return $restaurant && strtolower($restaurant->restaurant_supervisor_email) === strtolower($user->user_email);
        }
        
        return false;
    }

    /**
     * Display a listing of the resource.
     * Solo admin y supervisor pueden ver las mesas
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "status": true,
     *   "data": [
     *     {
     *       "table_id": 1,
     *       "restaurant_id": 1,
     *       "table_name": "T1",
     *       "table_capacity": 4,
     *       "table_zone": "Zona 1"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->user_role === 'customer') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Si es supervisor, solo ve las mesas de su restaurante
        if ($user->user_role === 'supervisor') {
            $restaurant = Restaurant::whereRaw('LOWER(restaurant_supervisor_email) = ?', strtolower($user->user_email))->first();
            if (!$restaurant) {
                return response()->json([
                    'status' => false,
                    'message' => 'No restaurant found for this supervisor'
                ], 404);
            }
            $tables = Table::where('restaurant_id', $restaurant->restaurant_id)->get();
        } else {
            // Si es admin, ve todas las mesas
            $tables = Table::all();
        }

        return response()->json([
            'status' => true,
            'data' => $tables
        ]);
    }

    /**
     * Create new table
     * Solo admin y supervisor del restaurante pueden crear mesas
     * 
     * @authenticated
     * 
     * @bodyParam restaurant_id integer required Restaurant ID
     * @bodyParam table_name string required Unique name within restaurant
     * @bodyParam table_capacity integer required Number of seats
     * @bodyParam table_zone string required Zone of the table
     * 
     * @response 201 {
     *   "status": true,
     *   "message": "Table created successfully",
     *   "data": {
     *     "table_id": 1,
     *     "restaurant_id": 1,
     *     "table_name": "T1",
     *     "table_capacity": 4,
     *     "table_zone": "Zona 1"
     *   }
     * }
     */
    public function store(Request $request)
    {
        dump('TableController@store: Starting');
        dump([
            'user' => auth()->user()->toArray(),
            'request' => $request->all()
        ]);

        if (!$this->checkRestaurantAccess($request->restaurant_id)) {
            dump('TableController@store: Access denied');
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,restaurant_id',
            'table_name' => 'required|string',
            'table_capacity' => 'required|integer|min:1',
            'table_zone' => 'required|string'
        ]);

        // Verificar nombre de mesa único para el restaurante
        $exists = Table::where('restaurant_id', $request->restaurant_id)
                      ->where('table_name', $request->table_name)
                      ->exists();
                      
        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Table name already exists in this restaurant'
            ], 422);
        }

        $table = Table::create($request->all());
        
        return response()->json([
            'status' => true,
            'message' => 'Table created successfully',
            'data' => $table
        ], 201);
    }

    /**
     * Display the specified resource.
     * Solo admin y supervisor del restaurante pueden ver una mesa
     * 
     * @authenticated
     * 
     * @urlParam table_id integer required Table ID
     * 
     * @response 200 {
     *   "status": true,
     *   "data": {
     *     "table_id": 1,
     *     "restaurant_id": 1,
     *     "table_name": "T1",
     *     "table_capacity": 4,
     *     "table_zone": "Zona 1"
     *   }
     * }
     */
    public function show(Table $table)
    {
        if (!$this->checkRestaurantAccess($table->restaurant_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data' => $table
        ]);
    }

    /**
     * Update the specified resource in storage.
     * Solo admin y supervisor del restaurante pueden actualizar una mesa
     * 
     * @authenticated
     * 
     * @urlParam table_id integer required Table ID
     * 
     * @bodyParam restaurant_id integer sometimes Restaurant ID
     * @bodyParam table_name string sometimes Unique name within restaurant
     * @bodyParam table_capacity integer sometimes Number of seats
     * @bodyParam table_zone string sometimes Zone of the table
     * @bodyParam table_status string sometimes Status of the table
     * 
     * @response 200 {
     *   "status": true,
     *   "message": "Table updated successfully",
     *   "data": {
     *     "table_id": 1,
     *     "restaurant_id": 1,
     *     "table_name": "T1",
     *     "table_capacity": 4,
     *     "table_zone": "Zona 1"
     *   }
     * }
     */
    public function update(Request $request, Table $table)
    {
        if (!$this->checkRestaurantAccess($table->restaurant_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'restaurant_id' => 'sometimes|exists:restaurants,restaurant_id',
            'table_name' => 'sometimes|string',
            'table_capacity' => 'sometimes|integer|min:1',
            'table_zone' => 'sometimes|string',
            'table_status' => 'sometimes|in:available,reserved,occupied,maintenance'
        ]);

        // Si se está actualizando el nombre de mesa, verificar que sea único
        if ($request->has('table_name') && 
            ($request->table_name !== $table->table_name || 
             ($request->has('restaurant_id') && $request->restaurant_id !== $table->restaurant_id))) {
            
            $exists = Table::where('restaurant_id', $request->restaurant_id ?? $table->restaurant_id)
                          ->where('table_name', $request->table_name)
                          ->where('table_id', '!=', $table->table_id)
                          ->exists();
            
            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Table name already exists in this restaurant'
                ], 422);
            }
        }

        $table->update($request->all());
        
        return response()->json([
            'status' => true,
            'message' => 'Table updated successfully',
            'data' => $table
        ]);
    }

    /**
     * Remove the specified resource from storage.
     * Solo admin y supervisor del restaurante pueden eliminar una mesa
     * 
     * @authenticated
     * 
     * @urlParam table_id integer required Table ID
     * 
     * @response 200 {
     *   "status": true,
     *   "message": "Table deleted successfully"
     * }
     */
    public function destroy(Table $table)
    {
        if (!$this->checkRestaurantAccess($table->restaurant_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $table->delete();

        return response()->json([
            'status' => true,
            'message' => 'Table deleted successfully'
        ]);
    }

    /**
     * List restaurant tables
     * Solo admin y supervisor del restaurante pueden ver las mesas
     * 
     * @authenticated
     * @urlParam restaurant_id integer required Restaurant ID
     * 
     * @response 200 {
     *   "status": true,
     *   "data": [
     *     {
     *       "table_id": 1,
     *       "restaurant_id": 1,
     *       "table_name": "T1",
     *       "table_capacity": 4,
     *       "table_zone": "Zona 1"
     *     }
     *   ]
     * }
     */
    public function getTablesByRestaurant($restaurant_id)
    {
        if (!$this->checkRestaurantAccess($restaurant_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $tables = Table::where('restaurant_id', $restaurant_id)->get();
        
        if ($tables->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No tables found for this restaurant'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $tables
        ]);
    }
}