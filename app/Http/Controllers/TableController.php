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
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $request->validate([
            'id_restaurant' => 'required|exists:restaurants,id_restaurant',
            'number' => 'required|string',
            'capacity' => 'required|integer|min:1'
        ]);

        // Verificar número de mesa único para el restaurante
        $exists = Table::where('id_restaurant', $request->id_restaurant)
                      ->where('number', $request->number)
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
    public function show($id_table)
    {
        $table = Table::with('restaurant')->find($id_table);
        
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
    public function update(Request $request, $id_table)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $table = Table::find($id_table);
        
        if (!$table) {
            return response()->json([
                'status' => false,
                'message' => 'Table not found'
            ], 404);
        }

        $request->validate([
            'number' => 'sometimes|string',
            'capacity' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:available,unavailable'
        ]);

        // Si están cambiando el número, verificar que no esté duplicado
        if ($request->has('number') && $request->number !== $table->number) {
            $exists = Table::where('id_restaurant', $table->id_restaurant)
                          ->where('number', $request->number)
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
    public function destroy($id_table)
    {
        if (auth()->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Admin access required'
            ], 403);
        }

        $table = Table::find($id_table);
        
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
        ], 200);
    }

    /**
     * Get tables by restaurant
     */
    public function getTablesByRestaurant($id_restaurant)
    {
        $tables = Table::where('id_restaurant', $id_restaurant)->get();
        
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