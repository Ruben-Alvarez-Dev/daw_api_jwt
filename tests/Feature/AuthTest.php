<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh');
    }

    /**
     * Pruebas de Registro
     */
    public function test_public_user_can_register_as_customer()
    {
        $response = $this->postJson('/api/register', [
            'user_name' => 'Test Customer',
            'user_email' => 'customer@test.com',
            'user_password' => 'password123',
            'user_password_confirmation' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'status' => true,
                    'message' => 'User registered successfully'
                ]);

        $this->assertDatabaseHas('users', [
            'user_email' => 'customer@test.com',
            'user_role' => 'customer'
        ]);
    }

    public function test_public_user_cannot_register_as_admin()
    {
        $response = $this->postJson('/api/register', [
            'user_name' => 'Test Admin',
            'user_email' => 'admin@test.com',
            'user_password' => 'password123',
            'user_password_confirmation' => 'password123',
            'user_role' => 'admin'
        ]);

        $response->assertStatus(403);
        
        $this->assertDatabaseMissing('users', [
            'user_email' => 'admin@test.com',
            'user_role' => 'admin'
        ]);
    }

    public function test_admin_can_create_any_user_type()
    {
        $admin = User::factory()->create([
            'user_role' => 'admin'
        ]);

        $response = $this->actingAs($admin)->postJson('/api/register', [
            'user_name' => 'New Supervisor',
            'user_email' => 'supervisor@test.com',
            'user_password' => 'password123',
            'user_password_confirmation' => 'password123',
            'user_role' => 'supervisor'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'user_email' => 'supervisor@test.com',
            'user_role' => 'supervisor',
            'user_created_by' => $admin->user_id
        ]);
    }

    public function test_supervisor_can_only_create_customers()
    {
        $supervisor = User::factory()->create([
            'user_role' => 'supervisor'
        ]);

        // Intento de crear admin (debe fallar)
        $response = $this->actingAs($supervisor)->postJson('/api/register', [
            'user_name' => 'New Admin',
            'user_email' => 'newadmin@test.com',
            'user_password' => 'password123',
            'user_password_confirmation' => 'password123',
            'user_role' => 'admin'
        ]);

        $response->assertStatus(403);

        // Crear customer (debe funcionar)
        $response = $this->actingAs($supervisor)->postJson('/api/register', [
            'user_name' => 'New Customer',
            'user_email' => 'newcustomer@test.com',
            'user_password' => 'password123',
            'user_password_confirmation' => 'password123',
            'user_role' => 'customer'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'user_email' => 'newcustomer@test.com',
            'user_role' => 'customer',
            'user_created_by' => $supervisor->user_id
        ]);
    }

    /**
     * Pruebas de Login
     */
    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'user_password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'user_email' => $user->user_email,
            'user_password' => 'password123'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'token',
                    'user' => [
                        'user_id',
                        'user_name',
                        'user_email',
                        'user_role'
                    ]
                ]);
    }

    public function test_user_cannot_login_with_incorrect_credentials()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login', [
            'user_email' => $user->user_email,
            'user_password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    'status' => false,
                    'message' => 'Invalid credentials'
                ]);
    }

    /**
     * Pruebas de Gestión de Usuarios
     */
    public function test_admin_can_view_all_users()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        User::factory()->count(3)->create(); // Crear algunos usuarios adicionales

        $response = $this->actingAs($admin)->getJson('/api/users');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'user_id',
                            'user_name',
                            'user_email',
                            'user_role',
                            'user_created_by'
                        ]
                    ]
                ]);
    }

    public function test_supervisor_can_only_view_created_users()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        
        // Usuarios creados por el supervisor
        $customer1 = User::factory()->create([
            'user_role' => 'customer',
            'user_created_by' => $supervisor->user_id
        ]);
        
        // Usuarios no creados por el supervisor
        $customer2 = User::factory()->create(['user_role' => 'customer']);

        $response = $this->actingAs($supervisor)->getJson('/api/users');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => true,
                    'data' => [
                        [
                            'user_id' => $customer1->user_id,
                            'user_created_by' => $supervisor->user_id
                        ]
                    ]
                ]);

        // Verificar que no puede ver usuarios que no creó
        $response->assertJsonMissing([
            'user_id' => $customer2->user_id
        ]);
    }

    public function test_customer_cannot_view_users_list()
    {
        $customer = User::factory()->create(['user_role' => 'customer']);

        $response = $this->actingAs($customer)->getJson('/api/users');

        $response->assertStatus(403);
    }

    public function test_user_can_view_own_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/profile');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => true,
                    'data' => [
                        'user_id' => $user->user_id,
                        'user_name' => $user->user_name,
                        'user_email' => $user->user_email,
                        'user_role' => $user->user_role
                    ]
                ]);
    }

    public function test_admin_can_update_any_user()
    {
        $admin = User::factory()->create(['user_role' => 'admin']);
        $user = User::factory()->create(['user_role' => 'customer']);

        $response = $this->actingAs($admin)->putJson("/api/users/{$user->user_id}", [
            'user_name' => 'Updated Name',
            'user_role' => 'supervisor'
        ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'user_name' => 'Updated Name',
            'user_role' => 'supervisor'
        ]);
    }

    public function test_supervisor_can_only_update_created_users()
    {
        $supervisor = User::factory()->create(['user_role' => 'supervisor']);
        
        $createdUser = User::factory()->create([
            'user_role' => 'customer',
            'user_created_by' => $supervisor->user_id
        ]);
        
        $otherUser = User::factory()->create(['user_role' => 'customer']);

        // Intentar actualizar usuario creado (debe funcionar)
        $response = $this->actingAs($supervisor)->putJson("/api/users/{$createdUser->user_id}", [
            'user_name' => 'Updated Name'
        ]);

        $response->assertStatus(200);

        // Intentar actualizar usuario no creado (debe fallar)
        $response = $this->actingAs($supervisor)->putJson("/api/users/{$otherUser->user_id}", [
            'user_name' => 'Updated Name'
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_can_only_update_own_profile()
    {
        $customer = User::factory()->create(['user_role' => 'customer']);
        $otherUser = User::factory()->create(['user_role' => 'customer']);

        // Actualizar propio perfil
        $response = $this->actingAs($customer)->putJson("/api/users/{$customer->user_id}", [
            'user_name' => 'Updated Name'
        ]);

        $response->assertStatus(200);

        // Intentar actualizar otro perfil
        $response = $this->actingAs($customer)->putJson("/api/users/{$otherUser->user_id}", [
            'user_name' => 'Updated Name'
        ]);

        $response->assertStatus(403);
    }

    public function test_user_logout()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->getJson('/api/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'status' => true,
                    'message' => 'Successfully logged out'
                ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'active_token' => null
        ]);
    }

    public function test_token_refresh()
    {
        $user = User::factory()->create();
        
        // Primero hacer login para obtener un token
        $loginResponse = $this->postJson('/api/login', [
            'user_email' => $user->user_email,
            'user_password' => 'password'
        ]);

        $token = $loginResponse->json()['token'];

        // Intentar refrescar el token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/refresh-token');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'token'
                ]);
    }
}
