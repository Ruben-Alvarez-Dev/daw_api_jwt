<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
   /**
    * Display a listing of the resource.
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
           'restaurant_name' => 'required|string|max:255'
       ]);

       $restaurant = Restaurant::create($request->all());
       return response()->json([
           'status' => true,
           'message' => 'Restaurant created successfully',
           'data' => $restaurant
       ], 201);
   }

   /**
    * Display the specified resource.
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
    * Update the specified resource in storage.
    */
   public function update(Request $request, $restaurant_id)
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

       $request->validate([
           'restaurant_name' => 'sometimes|string|max:255',
           'restaurant_capacity' => 'sometimes|integer|min:1',
           'restaurant_zones' => 'sometimes|array',
           'restaurant_is_active' => 'sometimes|boolean',
           'restaurant_status' => 'sometimes|in:tables available,fully booked'
       ]);

       $restaurant->update($request->all());
       return response()->json([
           'status' => true,
           'message' => 'Restaurant updated successfully',
           'data' => $restaurant
       ]);
   }

   /**
    * Remove the specified resource from storage.
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