<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Table;
use App\Models\Restaurant;
use Illuminate\Http\Request;

/**
 * Reservations Management API
 * 
 * @group Reservations
 */
class ReservationController extends Controller
{
    /**
     * Verifica si el usuario tiene acceso a la reserva
     */
    private function checkReservationAccess(Reservation $reservation)
    {
        $user = auth()->user();
        
        // Admin tiene acceso a todas las reservas
        if ($user->user_role === 'admin') {
            return true;
        }
        
        // Supervisor solo tiene acceso a reservas de su restaurante
        if ($user->user_role === 'supervisor') {
            $restaurant = Restaurant::find($reservation->restaurant_id);
            return $restaurant && $restaurant->restaurant_supervisor_email === $user->user_email;
        }
        
        // Customer solo tiene acceso a sus propias reservas
        return $user->user_id === $reservation->user_id;
    }

    /**
     * List reservations based on user role
     * - Admin: todas las reservas
     * - Supervisor: reservas de su restaurante
     * - Customer: sus propias reservas
     * 
     * @authenticated
     */
    public function index()
    {
        $user = auth()->user();
        $query = Reservation::with(['user', 'restaurant']);

        if ($user->user_role === 'admin') {
            $reservations = $query->get();
        } 
        elseif ($user->user_role === 'supervisor') {
            $restaurant = Restaurant::where('restaurant_supervisor_email', $user->user_email)->first();
            if (!$restaurant) {
                return response()->json(['message' => 'No restaurant found for supervisor'], 404);
            }
            $reservations = $query->where('restaurant_id', $restaurant->restaurant_id)->get();
        } 
        else {
            $reservations = $query->where('user_id', $user->user_id)->get();
        }

        return response()->json([
            'status' => true,
            'data' => $reservations
        ]);
    }

    /**
     * Create new reservation
     * - Customer: puede crear sus propias reservas
     * - Supervisor: puede crear reservas en su restaurante
     * - Admin: puede crear reservas en cualquier restaurante
     * 
     * @authenticated
     */
    public function store(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required|exists:restaurants,restaurant_id',
            'reservation_tables' => 'required|array',
            'reservation_datetime' => 'required|date|after:now',
            'reservation_status' => 'sometimes|in:pending,confirmed,seated,canceled,closed'
        ]);

        $user = auth()->user();
        
        // Si es supervisor, verificar que el restaurante le pertenece
        if ($user->user_role === 'supervisor') {
            $restaurant = Restaurant::find($request->restaurant_id);
            if (!$restaurant || $restaurant->restaurant_supervisor_email !== $user->user_email) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized - Not your restaurant'
                ], 403);
            }
        }

        // Verificar disponibilidad de mesas
        foreach ($request->reservation_tables as $tableNumber) {
            $table = Table::where('restaurant_id', $request->restaurant_id)
                         ->where('table_number', $tableNumber)
                         ->where('table_status', 'available')
                         ->first();
            
            if (!$table) {
                return response()->json([
                    'status' => false,
                    'message' => "Table {$tableNumber} is not available"
                ], 422);
            }
        }

        // Si es supervisor o admin, pueden crear reservas para otros usuarios
        $userId = $request->user_id ?? auth()->id();
        
        // Si es customer, solo puede crear reservas para sí mismo
        if ($user->user_role === 'customer' && $userId !== $user->user_id) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized - Cannot create reservations for other users'
            ], 403);
        }

        // Crear la reserva
        $reservation = Reservation::create([
            'user_id' => $userId,
            'restaurant_id' => $request->restaurant_id,
            'reservation_tables' => $request->reservation_tables,
            'reservation_datetime' => $request->reservation_datetime,
            'reservation_status' => $request->reservation_status ?? 'pending'
        ]);

        // Actualizar estado de las mesas
        Table::whereIn('table_number', $request->reservation_tables)
             ->where('restaurant_id', $request->restaurant_id)
             ->update(['table_status' => 'reserved']);

        return response()->json([
            'status' => true,
            'message' => 'Reservation created successfully',
            'data' => $reservation->load(['user', 'restaurant'])
        ], 201);
    }

    /**
     * Get reservation details
     * Solo accesible si el usuario tiene permisos sobre la reserva
     * 
     * @authenticated
     */
    public function show(Reservation $reservation)
    {
        if (!$this->checkReservationAccess($reservation)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data' => $reservation->load(['user', 'restaurant'])
        ]);
    }

    /**
     * Update reservation details
     * Solo si el usuario tiene permisos sobre la reserva
     * 
     * @authenticated
     */
    public function update(Request $request, Reservation $reservation)
    {
        if (!$this->checkReservationAccess($reservation)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $request->validate([
            'reservation_tables' => 'sometimes|array',
            'reservation_datetime' => 'sometimes|date|after:now',
            'reservation_status' => 'sometimes|in:pending,confirmed,seated,canceled,closed'
        ]);

        // Solo supervisor y admin pueden cambiar el estado a 'confirmed' o 'seated'
        if ($request->has('reservation_status')) {
            if (in_array($request->reservation_status, ['confirmed', 'seated']) && 
                auth()->user()->user_role === 'customer') {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized - Cannot confirm or seat reservations'
                ], 403);
            }
        }

        if ($request->has('reservation_status') && $request->reservation_status === 'canceled') {
            // Liberar las mesas si se cancela
            Table::whereIn('table_number', $reservation->reservation_tables)
                 ->where('restaurant_id', $reservation->restaurant_id)
                 ->update(['table_status' => 'available']);
        }

        $reservation->update($request->all());
        return response()->json([
            'status' => true,
            'message' => 'Reservation updated successfully',
            'data' => $reservation->load(['user', 'restaurant'])
        ]);
    }

    /**
     * Delete reservation
     * Solo si el usuario tiene permisos sobre la reserva
     * 
     * @authenticated
     */
    public function destroy(Reservation $reservation)
    {
        if (!$this->checkReservationAccess($reservation)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Liberar las mesas antes de eliminar
        Table::whereIn('table_number', $reservation->reservation_tables)
             ->where('restaurant_id', $reservation->restaurant_id)
             ->update(['table_status' => 'available']);

        $reservation->delete();
        return response()->json([
            'status' => true,
            'message' => 'Reservation deleted successfully'
        ]);
    }

    /**
     * Get reservations for a specific restaurant
     * Solo accesible para admin y supervisor del restaurante
     * 
     * @authenticated
     */
    public function getReservationsByRestaurant($restaurant_id)
    {
        $user = auth()->user();
        $restaurant = Restaurant::find($restaurant_id);

        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }

        // Verificar permisos
        if ($user->user_role !== 'admin' && 
            ($user->user_role !== 'supervisor' || $restaurant->restaurant_supervisor_email !== $user->user_email)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 403);
        }

        $reservations = Reservation::with(['user', 'restaurant'])
            ->where('restaurant_id', $restaurant_id)
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reservations
        ]);
    }

    /**
     * Get user's own reservations
     * Cada usuario puede ver sus propias reservas
     * 
     * @authenticated
     */
    public function getUserReservations()
    {
        $reservations = Reservation::with(['restaurant'])
            ->where('user_id', auth()->id())
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reservations
        ]);
    }
}