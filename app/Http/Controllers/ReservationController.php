<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with(['user', 'restaurant'])->get();
        return response()->json($reservations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_restaurant' => 'required|exists:restaurants,id_restaurant',
            'tables' => 'required|array',
            'datetime' => 'required|date|after:now',
            'status' => 'sometimes|in:pending,confirmed,seated,canceled,closed'
        ]);

        // Verificar disponibilidad de mesas
        foreach ($request->tables as $tableNumber) {
            $table = Table::where('id_restaurant', $request->id_restaurant)
                         ->where('number', $tableNumber)
                         ->where('status', 'available')
                         ->first();
            
            if (!$table) {
                return response()->json([
                    'message' => "Table {$tableNumber} is not available"
                ], 422);
            }
        }

        // Crear la reserva
        $reservation = Reservation::create([
            'id_user' => auth()->id(), // Asumiendo que usas autenticación
            'id_restaurant' => $request->id_restaurant,
            'tables' => $request->tables,
            'datetime' => $request->datetime,
            'status' => $request->status ?? 'pending'
        ]);

        // Actualizar estado de las mesas
        Table::whereIn('number', $request->tables)
             ->where('id_restaurant', $request->id_restaurant)
             ->update(['status' => 'unavailable']);

        return response()->json($reservation->load(['user', 'restaurant']), 201);
    }

    public function show(Reservation $reservation)
    {
        return response()->json($reservation->load(['user', 'restaurant']));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'tables' => 'sometimes|array',
            'datetime' => 'sometimes|date|after:now',
            'status' => 'sometimes|in:pending,confirmed,seated,canceled,closed'
        ]);

        if ($request->has('status') && $request->status === 'canceled') {
            // Liberar las mesas si se cancela
            Table::whereIn('number', $reservation->tables)
                 ->where('id_restaurant', $reservation->id_restaurant)
                 ->update(['status' => 'available']);
        }

        $reservation->update($request->all());
        return response()->json($reservation->load(['user', 'restaurant']));
    }

    public function destroy(Reservation $reservation)
    {
        // Liberar las mesas antes de eliminar
        Table::whereIn('number', $reservation->tables)
             ->where('id_restaurant', $reservation->id_restaurant)
             ->update(['status' => 'available']);

        $reservation->delete();
        return response()->json(null, 204);
    }
}