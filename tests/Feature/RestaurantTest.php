<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    /**
     * Pruebas de Listado de Restaurantes
     */
    public function test_anyone_can_view_restaurants_list()
    {
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => 'supervisor@test.com',
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response = $this->getJson('/api/restaurants');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'restaurant_id',
                            'restaurant_name',
                            'restaurant_business_name',
                            'restaurant_food_type',
                            'restaurant_capacity',
                            'restaurant_business_email',
                            'restaurant_supervisor_email',
                            'restaurant_phone',
                            'restaurant_description',
                            'restaurant_zones'
                        ]
                    ]
                ]);
    }

    /**
     * Pruebas de Creación de Restaurantes
     */
    public function test_admin_can_create_restaurant()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);

        $response = $this->actingAs($admin)->postJson('/api/restaurants', [
            'restaurant_name' => 'New Restaurant',
            'restaurant_business_name' => 'New Business',
            'restaurant_food_type' => 'New Food',
            'restaurant_capacity' => 100,
            'restaurant_business_email' => 'new@business.com',
            'restaurant_supervisor_email' => 'new@supervisor.com',
            'restaurant_phone' => '987654321',
            'restaurant_description' => 'New Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('restaurants', [
            'restaurant_name' => 'New Restaurant',
            'restaurant_business_email' => 'new@business.com'
        ]);
    }

    public function test_supervisor_cannot_create_restaurant()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);

        $response = $this->actingAs($supervisor)->postJson('/api/restaurants', [
            'restaurant_name' => 'New Restaurant',
            'restaurant_business_name' => 'New Business',
            'restaurant_food_type' => 'New Food',
            'restaurant_capacity' => 100,
            'restaurant_business_email' => 'new@business.com',
            'restaurant_supervisor_email' => 'new@supervisor.com',
            'restaurant_phone' => '987654321',
            'restaurant_description' => 'New Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response->assertStatus(403);
    }

    /**
     * Pruebas de Actualización de Restaurantes
     */
    public function test_admin_can_update_any_restaurant()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => 'supervisor@test.com',
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response = $this->actingAs($admin)->putJson("/api/restaurants/{$restaurant->restaurant_id}", [
            'restaurant_name' => 'Updated Restaurant',
            'restaurant_capacity' => 75
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('restaurants', [
            'restaurant_id' => $restaurant->restaurant_id,
            'restaurant_name' => 'Updated Restaurant',
            'restaurant_capacity' => 75
        ]);
    }

    public function test_supervisor_can_update_own_restaurant()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => $supervisor->user_email,
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response = $this->actingAs($supervisor)->putJson("/api/restaurants/{$restaurant->restaurant_id}", [
            'restaurant_name' => 'Updated Restaurant',
            'restaurant_capacity' => 75
        ]);

        $response->assertStatus(200);
    }

    public function test_supervisor_cannot_update_other_restaurant()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => 'other@supervisor.com',
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response = $this->actingAs($supervisor)->putJson("/api/restaurants/{$restaurant->restaurant_id}", [
            'restaurant_name' => 'Updated Restaurant'
        ]);

        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_change_supervisor_email()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => $supervisor->user_email,
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response = $this->actingAs($supervisor)->putJson("/api/restaurants/{$restaurant->restaurant_id}", [
            'restaurant_supervisor_email' => 'new@supervisor.com'
        ]);

        $response->assertStatus(403);
    }

    /**
     * Pruebas de Eliminación de Restaurantes
     */
    public function test_admin_can_delete_restaurant()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => 'supervisor@test.com',
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/restaurants/{$restaurant->restaurant_id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('restaurants', [
            'restaurant_id' => $restaurant->restaurant_id
        ]);
    }

    public function test_supervisor_cannot_delete_restaurant()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => $supervisor->user_email,
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response = $this->actingAs($supervisor)->deleteJson("/api/restaurants/{$restaurant->restaurant_id}");

        $response->assertStatus(403);
        
        $this->assertDatabaseHas('restaurants', [
            'restaurant_id' => $restaurant->restaurant_id
        ]);
    }
}
