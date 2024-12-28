<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'reservation_user_id' => 'required|exists:users,id',
            'reservation_restaurant_id' => 'required|exists:restaurants,restaurant_id',
            'reservation_tables_ids' => 'required|string', // Validación básica, se podría mejorar
            'reservation_datetime' => 'required|date|after:now',
            'reservation_status' => 'sometimes|in:pending,confirmed,seated,cancelled,no_show'
        ]);

        $reservation = Reservation::create($request->all());
        return response()->json($reservation, 201);
    }

    public function show(Reservation $reservation)
    {
        return response()->json($reservation->load('user', 'restaurant'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'reservation_user_id' => 'sometimes|exists:users,id',
            'reservation_restaurant_id' => 'sometimes|exists:restaurants,restaurant_id',
            'reservation_tables_ids' => 'sometimes|string',
            'reservation_datetime' => 'sometimes|date|after:now',
            'reservation_status' => 'sometimes|in:pending,confirmed,seated,cancelled,no_show'
        ]);

        $reservation->update($request->all());
        return response()->json($reservation);
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $request->validate([
            'reservation_status' => 'required|in:pending,confirmed,seated,cancelled,no_show'
        ]);

        $reservation->update([
            'reservation_status' => $request->reservation_status
        ]);

        return response()->json($reservation);
    }

    // Método adicional para obtener las reservas del usuario autenticado
    public function myReservations()
    {
        $reservations = Reservation::where('reservation_user_id', Auth::id())
            ->with(['restaurant'])
            ->get();
            
        return response()->json($reservations);
    }
}
