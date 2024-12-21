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
            'restaurant_id' => 'required|exists:restaurants,restaurant_id',
            'reservation_tables' => 'required|array',
            'reservation_datetime' => 'required|date|after:now',
            'reservation_status' => 'sometimes|in:pending,confirmed,seated,canceled,closed'
        ]);

        // Verificar disponibilidad de mesas
        foreach ($request->reservation_tables as $tableNumber) {
            $table = Table::where('restaurant_id', $request->restaurant_id)
                         ->where('table_number', $tableNumber)
                         ->where('table_status', 'available')
                         ->first();
            
            if (!$table) {
                return response()->json([
                    'message' => "Table {$tableNumber} is not available"
                ], 422);
            }
        }

        // Crear la reserva
        $reservation = Reservation::create([
            'user_id' => auth()->user()->user_id,
            'restaurant_id' => $request->restaurant_id,
            'reservation_tables' => $request->reservation_tables,
            'reservation_datetime' => $request->reservation_datetime,
            'reservation_status' => $request->reservation_status ?? 'pending'
        ]);

        // Actualizar estado de las mesas
        Table::whereIn('table_number', $request->reservation_tables)
             ->where('restaurant_id', $request->restaurant_id)
             ->update(['table_status' => 'unavailable']);

        return response()->json($reservation->load(['user', 'restaurant']), 201);
    }

    public function show(Reservation $reservation)
    {
        return response()->json($reservation->load(['user', 'restaurant']));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'reservation_tables' => 'sometimes|array',
            'reservation_datetime' => 'sometimes|date|after:now',
            'reservation_status' => 'sometimes|in:pending,confirmed,seated,canceled,closed'
        ]);

        if ($request->has('reservation_status') && $request->reservation_status === 'canceled') {
            // Liberar las mesas si se cancela
            Table::whereIn('table_number', $reservation->reservation_tables)
                 ->where('restaurant_id', $reservation->restaurant_id)
                 ->update(['table_status' => 'available']);
        }

        $reservation->update($request->all());
        return response()->json($reservation->load(['user', 'restaurant']));
    }

    public function destroy(Reservation $reservation)
    {
        // Liberar las mesas antes de eliminar
        Table::whereIn('table_number', $reservation->reservation_tables)
             ->where('restaurant_id', $reservation->restaurant_id)
             ->update(['table_status' => 'available']);

        $reservation->delete();
        return response()->json(null, 204);
    }
}