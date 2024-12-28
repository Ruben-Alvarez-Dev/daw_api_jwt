<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $zones = Zone::with(['restaurant', 'tables'])->get();
        return response()->json($zones);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'zone_restaurant_id' => 'required|exists:restaurants,restaurant_id',
            'zone_name' => 'required|string|max:255'
        ]);

        $zone = Zone::create($request->all());
        return response()->json($zone, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Zone $zone)
    {
        return response()->json($zone->load('restaurant', 'tables'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Zone $zone)
    {
        $request->validate([
            'zone_restaurant_id' => 'sometimes|exists:restaurants,restaurant_id',
            'zone_name' => 'sometimes|string|max:255'
        ]);

        $zone->update($request->all());
        return response()->json($zone);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zone $zone)
    {
        $zone->delete();
        return response()->json(null, 204);
    }

    /**
     * Display the tables of the specified resource.
     */
    public function tables(Zone $zone)
    {
        return response()->json($zone->tables);
    }
}
