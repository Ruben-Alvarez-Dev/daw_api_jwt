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
       if (auth()->user()->role !== 'admin') {
           return response()->json([
               'status' => false,
               'message' => 'Unauthorized - Admin access required'
           ], 403);
       }

       $request->validate([
           'name' => 'required|string|max:255'
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
   public function show($id_restaurant)
   {
       $restaurant = Restaurant::find($id_restaurant);
       
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
   public function update(Request $request, $id_restaurant)
   {
       if (auth()->user()->role !== 'admin') {
           return response()->json([
               'status' => false,
               'message' => 'Unauthorized - Admin access required'
           ], 403);
       }

       $restaurant = Restaurant::find($id_restaurant);
       
       if (!$restaurant) {
           return response()->json([
               'status' => false,
               'message' => 'Restaurant not found'
           ], 404);
       }

       $request->validate([
           'name' => 'sometimes|string|max:255',
           'capacity' => 'sometimes|integer|min:1',
           'zones' => 'sometimes|array',
           'isActive' => 'sometimes|boolean',
           'status' => 'sometimes|in:tables available,fully booked'
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
   public function destroy($id_restaurant)
   {
       if (auth()->user()->role !== 'admin') {
           return response()->json([
               'status' => false,
               'message' => 'Unauthorized - Admin access required'
           ], 403);
       }

       $restaurant = Restaurant::find($id_restaurant);
       
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
       ], 200);
   }
}