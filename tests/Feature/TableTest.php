<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    private function createRestaurant($supervisorEmail)
    {
        return Restaurant::create([
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
    }

    /**
     * Pruebas de Listado de Mesas
     */
    public function test_admin_can_view_all_tables()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $restaurant = $this->createRestaurant('supervisor@test.com');
        
        Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response = $this->actingAs($admin)->getJson('/api/tables');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'table_id',
                            'restaurant_id',
                            'table_name',
                            'table_capacity',
                            'table_zone',
                            'table_status'
                        ]
                    ]
                ]);
    }

    public function test_supervisor_can_only_view_restaurant_tables()
    {
        // Primero, creamos un admin que será quien cree los restaurantes
        $admin = User::factory()->create(['user_role' => 'admin']);
        
        // El admin crea un supervisor
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        
        // El admin crea dos restaurantes, uno asignado al supervisor
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant 1',
            'restaurant_business_name' => 'Test Business 1',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business1@test.com',
            'restaurant_supervisor_email' => $supervisor->user_email,
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);
        
        // El admin crea otro restaurante con otro supervisor
        $otherRestaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant 2',
            'restaurant_business_name' => 'Test Business 2',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business2@test.com',
            'restaurant_supervisor_email' => 'other@test.com',
            'restaurant_phone' => '987654321',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);
        
        // Mesa en el restaurante del supervisor
        $table1 = Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        // Mesa en otro restaurante
        $table2 = Table::create([
            'restaurant_id' => $otherRestaurant->restaurant_id,
            'table_name' => 'T2',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        // El supervisor intenta ver las mesas
        $response = $this->actingAs($supervisor)->getJson('/api/tables');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => true,
                    'data' => [
                        [
                            'table_id' => $table1->table_id,
                            'restaurant_id' => $restaurant->restaurant_id
                        ]
                    ]
                ]);

        // Verificar que no puede ver mesas de otro restaurante
        $response->assertJsonMissing([
            'table_id' => $table2->table_id
        ]);
    }

    public function test_customer_cannot_view_tables()
    {
        $customer = User::factory()->create(['user_role' => 'customer']);
        $restaurant = $this->createRestaurant('supervisor@test.com');
        
        Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response = $this->actingAs($customer)->getJson('/api/tables');

        $response->assertStatus(403);
    }

    /**
     * Pruebas de Creación de Mesas
     */
    public function test_admin_can_create_table()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $restaurant = $this->createRestaurant('supervisor@test.com');

        $response = $this->actingAs($admin)->postJson('/api/tables', [
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('tables', [
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1'
        ]);
    }

    public function test_supervisor_can_create_table_in_own_restaurant()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
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

        $response = $this->actingAs($supervisor)->postJson('/api/tables', [
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response->assertStatus(201);
    }

    public function test_supervisor_cannot_create_table_in_other_restaurant()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => 'other@test.com',
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);

        $response = $this->actingAs($supervisor)->postJson('/api/tables', [
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response->assertStatus(403);
    }

    /**
     * Tests de validación de datos
     */
    public function test_table_creation_requires_all_fields()
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

        $response = $this->actingAs($admin)->postJson('/api/tables', []);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['restaurant_id', 'table_name', 'table_capacity', 'table_zone']);

        $response = $this->actingAs($admin)->postJson('/api/tables', [
            'restaurant_id' => $restaurant->restaurant_id
        ]);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['table_name', 'table_capacity', 'table_zone']);
    }

    public function test_table_creation_validates_data_types()
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

        $response = $this->actingAs($admin)->postJson('/api/tables', [
            'restaurant_id' => 'not-a-number',
            'table_name' => [],  // debería ser string
            'table_capacity' => 'not-a-number',
            'table_zone' => []   // debería ser string
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['restaurant_id', 'table_name', 'table_capacity', 'table_zone']);
    }

    public function test_table_name_must_be_unique_in_restaurant()
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

        // Primera mesa - debería crearse
        $response = $this->actingAs($admin)->postJson('/api/tables', [
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1'
        ]);
        $response->assertStatus(201);

        // Segunda mesa con el mismo nombre - debería fallar
        $response = $this->actingAs($admin)->postJson('/api/tables', [
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1'
        ]);
        $response->assertStatus(422)
                ->assertJson([
                    'status' => false,
                    'message' => 'Table name already exists in this restaurant'
                ]);
    }

    /**
     * Tests de acceso para cliente y usuario no autenticado
     */
    public function test_unauthenticated_user_cannot_access_tables()
    {
        $response = $this->getJson('/api/tables');
        $response->assertStatus(401);

        $response = $this->postJson('/api/tables', [
            'restaurant_id' => 1,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1'
        ]);
        $response->assertStatus(401);

        $response = $this->putJson('/api/tables/1', [
            'table_name' => 'T1-Updated'
        ]);
        $response->assertStatus(401);

        $response = $this->deleteJson('/api/tables/1');
        $response->assertStatus(401);
    }

    public function test_customer_cannot_create_table()
    {
        $customer = User::factory()->create(['user_role' => 'customer']);
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

        $response = $this->actingAs($customer)->postJson('/api/tables', [
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1'
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_update_table()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $customer = User::factory()->create(['user_role' => 'customer']);
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

        // Admin crea la mesa
        $table = Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        // Cliente intenta actualizar
        $response = $this->actingAs($customer)->putJson("/api/tables/{$table->table_id}", [
            'table_name' => 'T1-Updated'
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_delete_table()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $customer = User::factory()->create(['user_role' => 'customer']);
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

        // Admin crea la mesa
        $table = Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        // Cliente intenta eliminar
        $response = $this->actingAs($customer)->deleteJson("/api/tables/{$table->table_id}");

        $response->assertStatus(403);
    }

    /**
     * Pruebas de Actualización de Mesas
     */
    public function test_admin_can_update_any_table()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $restaurant = $this->createRestaurant('supervisor@test.com');
        
        $table = Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response = $this->actingAs($admin)->putJson("/api/tables/{$table->table_id}", [
            'table_capacity' => 6
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('tables', [
            'table_id' => $table->table_id,
            'table_capacity' => 6
        ]);
    }

    public function test_supervisor_can_update_restaurant_table()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
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
        
        $table = Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response = $this->actingAs($supervisor)->putJson("/api/tables/{$table->table_id}", [
            'table_capacity' => 6
        ]);

        $response->assertStatus(200);
    }

    public function test_supervisor_cannot_update_other_restaurant_table()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => 'other@test.com',
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);
        
        $table = Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response = $this->actingAs($supervisor)->putJson("/api/tables/{$table->table_id}", [
            'table_capacity' => 6
        ]);

        $response->assertStatus(403);
    }

    /**
     * Pruebas de Eliminación de Mesas
     */
    public function test_admin_can_delete_table()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $restaurant = $this->createRestaurant('supervisor@test.com');
        
        $table = Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/tables/{$table->table_id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('tables', [
            'table_id' => $table->table_id
        ]);
    }

    public function test_supervisor_can_delete_restaurant_table()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
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
        
        $table = Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response = $this->actingAs($supervisor)->deleteJson("/api/tables/{$table->table_id}");

        $response->assertStatus(200);
    }

    public function test_supervisor_cannot_delete_other_restaurant_table()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        $restaurant = Restaurant::create([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_business_name' => 'Test Business',
            'restaurant_food_type' => 'Test Food',
            'restaurant_capacity' => 50,
            'restaurant_business_email' => 'business@test.com',
            'restaurant_supervisor_email' => 'other@test.com',
            'restaurant_phone' => '123456789',
            'restaurant_description' => 'Test Description',
            'restaurant_zones' => json_encode(['zone1', 'zone2'])
        ]);
        
        $table = Table::create([
            'restaurant_id' => $restaurant->restaurant_id,
            'table_name' => 'T1',
            'table_capacity' => 4,
            'table_zone' => 'zone1',
            'table_status' => 'available'
        ]);

        $response = $this->actingAs($supervisor)->deleteJson("/api/tables/{$table->table_id}");

        $response->assertStatus(403);
        
        $this->assertDatabaseHas('tables', [
            'table_id' => $table->table_id
        ]);
    }
}