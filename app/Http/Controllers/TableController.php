<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tables = Table::with('restaurant')->get();
        return response()->json([
            'status' => true,
            'data' => $tables
        ]);
    }

    /**
     * Store a newly created resource in storage.
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
            'restaurant_id' => 'required|exists:restaurants,restaurant_id',
            'table_number' => 'required|string',
            'table_capacity' => 'required|integer|min:1'
        ]);

        // Verificar número de mesa único para el restaurante
        $exists = Table::where('restaurant_id', $request->restaurant_id)
                      ->where('table_number', $request->table_number)
                      ->exists();
                      
        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'Table number already exists in this restaurant'
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
     */
    public function show($table_id)
    {
        $table = Table::with('restaurant')->find($table_id);
        
        if (!$table) {
            return response()->json([
                'status' => false,
                'message' => 'Table not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $table
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $table_id)
    {
        if (auth()->user()->user_role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $table = Table::find($table_id);
        
        if (!$table) {
            return response()->json([
                'status' => false,
                'message' => 'Table not found'
            ], 404);
        }

        $request->validate([
            'restaurant_id' => 'sometimes|exists:restaurants,restaurant_id',
            'table_number' => 'sometimes|string',
            'table_capacity' => 'sometimes|integer|min:1',
            'table_status' => 'sometimes|in:available,unavailable'
        ]);

        // Si se está actualizando el número de mesa, verificar que sea único
        if ($request->has('table_number') && 
            ($request->table_number !== $table->table_number || 
             ($request->has('restaurant_id') && $request->restaurant_id !== $table->restaurant_id))) {
            
            $exists = Table::where('restaurant_id', $request->restaurant_id ?? $table->restaurant_id)
                          ->where('table_number', $request->table_number)
                          ->where('table_id', '!=', $table_id)
                          ->exists();
            
            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Table number already exists in this restaurant'
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
     */
    public function destroy($table_id)
    {
        if (auth()->user()->user_role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $table = Table::find($table_id);
        
        if (!$table) {
            return response()->json([
                'status' => false,
                'message' => 'Table not found'
            ], 404);
        }

        $table->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Table deleted successfully'
        ]);
    }

    /**
     * Get tables by restaurant
     */
    public function getTablesByRestaurant($restaurant_id)
    {
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