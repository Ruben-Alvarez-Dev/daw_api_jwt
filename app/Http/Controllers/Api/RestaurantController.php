<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestaurantController extends Controller
{
    public function index()
    {
        $restaurants = Restaurant::with('supervisor')->get();
        return response()->json($restaurants);
    }

    public function store(Request $request)
    {
        $request->validate([
            'restaurant_name' => 'required|string|max:255',
            'restaurant_supervisor_id' => 'required|exists:users,id',
            'restaurant_max_capacity' => 'required|integer|min:1',
            'restaurant_starttime' => 'required|date_format:H:i',
            'restaurant_endtime' => 'required|date_format:H:i|after:restaurant_starttime',
            'restaurant_intervals' => 'required|integer|min:15|max:120'
        ]);

        $restaurant = Restaurant::create($request->all());
        return response()->json($restaurant, 201);
    }

    public function show(Restaurant $restaurant)
    {
        return response()->json($restaurant->load('supervisor', 'zones'));
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'restaurant_name' => 'sometimes|string|max:255',
            'restaurant_supervisor_id' => 'sometimes|exists:users,id',
            'restaurant_max_capacity' => 'sometimes|integer|min:1',
            'restaurant_starttime' => 'sometimes|date_format:H:i',
            'restaurant_endtime' => 'sometimes|date_format:H:i|after:restaurant_starttime',
            'restaurant_intervals' => 'sometimes|integer|min:15|max:120'
        ]);

        $restaurant->update($request->all());
        return response()->json($restaurant);
    }

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return response()->json(null, 204);
    }

    public function zones(Restaurant $restaurant)
    {
        return response()->json($restaurant->zones()->with('tables')->get());
    }
}
