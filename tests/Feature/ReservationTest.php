<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    private function createRestaurantWithTables($supervisorEmail)
    {
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => $supervisorEmail,
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_number' => 1,
            'table_capacity' => 4,
            'table_status' => 'available'
        ]);

        Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_number' => 2,
            'table_capacity' => 4,
            'table_status' => 'available'
        ]);

        return $restaurant;
    }

    /**
     * Pruebas de Listado de Reservas
     */
    public function test_admin_can_view_all_reservations()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $restaurant = $this->createRestaurantWithTables('supervisor@test.com');
        
        // Crear algunas reservas
        Reservation::create([
            'user_id' => User::factory()->create()->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->getJson('/api/reservations');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'reservation_id',
                            'user_id',
                            'restaurant_id',
                            'reservation_tables',
                            'reservation_datetime',
                            'reservation_status'
                        ]
                    ]
                ]);
    }

    public function test_supervisor_can_only_view_restaurant_reservations()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = $this->createRestaurantWithTables($supervisor->user_email);
        
        // Reserva en el restaurante del supervisor
        $reservation1 = Reservation::create([
            'user_id' => User::factory()->create()->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        // Reserva en otro restaurante
        $otherRestaurant = $this->createRestaurantWithTables('other@test.com');
        $reservation2 = Reservation::create([
            'user_id' => User::factory()->create()->user_id,
            'restaurant_id' => $otherRestaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        $response = $this->actingAs($supervisor)->getJson('/api/reservations');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => true,
                    'data' => [
                        [
                            'reservation_id' => $reservation1->reservation_id,
                            'restaurant_id' => $restaurant->restaurant_id
                        ]
                    ]
                ]);

        // Verificar que no puede ver reservas de otro restaurante
        $response->assertJsonMissing([
            'reservation_id' => $reservation2->reservation_id
        ]);
    }

    public function test_customer_can_only_view_own_reservations()
    {
        $customer = User::factory()->create(['user_role' => 'customer']);
        $restaurant = $this->createRestaurantWithTables('supervisor@test.com');
        
        // Reserva del customer
        $reservation1 = Reservation::create([
            'user_id' => $customer->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        // Reserva de otro usuario
        $reservation2 = Reservation::create([
            'user_id' => User::factory()->create()->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [2],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        $response = $this->actingAs($customer)->getJson('/api/reservations');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => true,
                    'data' => [
                        [
                            'reservation_id' => $reservation1->reservation_id,
                            'user_id' => $customer->user_id
                        ]
                    ]
                ]);

        // Verificar que no puede ver reservas de otros usuarios
        $response->assertJsonMissing([
            'reservation_id' => $reservation2->reservation_id
        ]);
    }

    /**
     * Pruebas de Creación de Reservas
     */
    public function test_customer_can_create_reservation()
    {
        $customer = User::factory()->create(['user_role' => 'customer']);
        $restaurant = $this->createRestaurantWithTables('supervisor@test.com');

        $response = $this->actingAs($customer)->postJson('/api/reservations', [
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'reservation_status' => 'pending'
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('reservations', [
            'user_id' => $customer->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_status' => 'pending'
        ]);
    }

    public function test_supervisor_can_create_reservation_for_others()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $customer = User::factory()->create(['user_role' => 'customer']);
        $restaurant = $this->createRestaurantWithTables($supervisor->user_email);

        $response = $this->actingAs($supervisor)->postJson('/api/reservations', [
            'user_id' => $customer->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay()->format('Y-m-d H:i:s'),
            'reservation_status' => 'confirmed'
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('reservations', [
            'user_id' => $customer->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_status' => 'confirmed'
        ]);
    }

    /**
     * Pruebas de Actualización de Reservas
     */
    public function test_customer_can_update_own_reservation()
    {
        $customer = User::factory()->create(['user_role' => 'customer']);
        $restaurant = $this->createRestaurantWithTables('supervisor@test.com');
        
        $reservation = Reservation::create([
            'user_id' => $customer->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        $response = $this->actingAs($customer)->putJson("/api/reservations/{$reservation->reservation_id}", [
            'reservation_datetime' => now()->addDays(2)->format('Y-m-d H:i:s')
        ]);

        $response->assertStatus(200);
    }

    public function test_customer_cannot_confirm_own_reservation()
    {
        $customer = User::factory()->create(['user_role' => 'customer']);
        $restaurant = $this->createRestaurantWithTables('supervisor@test.com');
        
        $reservation = Reservation::create([
            'user_id' => $customer->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        $response = $this->actingAs($customer)->putJson("/api/reservations/{$reservation->reservation_id}", [
            'reservation_status' => 'confirmed'
        ]);

        $response->assertStatus(403);
    }

    public function test_supervisor_can_confirm_reservation()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = $this->createRestaurantWithTables($supervisor->user_email);
        
        $reservation = Reservation::create([
            'user_id' => User::factory()->create()->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        $response = $this->actingAs($supervisor)->putJson("/api/reservations/{$reservation->reservation_id}", [
            'reservation_status' => 'confirmed'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('reservations', [
            'reservation_id' => $reservation->reservation_id,
            'reservation_status' => 'confirmed'
        ]);
    }

    /**
     * Pruebas de Eliminación de Reservas
     */
    public function test_customer_can_cancel_own_reservation()
    {
        $customer = User::factory()->create(['user_role' => 'customer']);
        $restaurant = $this->createRestaurantWithTables('supervisor@test.com');
        
        $reservation = Reservation::create([
            'user_id' => $customer->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        $response = $this->actingAs($customer)->deleteJson("/api/reservations/{$reservation->reservation_id}");

        $response->assertStatus(200);
        
        // Verificar que las mesas se liberaron
        $this->assertDatabaseHas('tables', [
            'restaurant_id' => $restaurant->restaurant_id,
            'table_number' => 1,
            'table_status' => 'available'
        ]);
    }

    public function test_supervisor_can_cancel_any_reservation_in_restaurant()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = $this->createRestaurantWithTables($supervisor->user_email);
        
        $reservation = Reservation::create([
            'user_id' => User::factory()->create()->user_id,
            'restaurant_id' => $restaurant->restaurant_id,
            'reservation_tables' => [1],
            'reservation_datetime' => now()->addDay(),
            'reservation_status' => 'pending'
        ]);

        $response = $this->actingAs($supervisor)->deleteJson("/api/reservations/{$reservation->reservation_id}");

        $response->assertStatus(200);
    }
}
