<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestSeeder extends Seeder
{
    public function run()
    {
        // 1. Insertar un usuario
        $userId = DB::table('users')->insertGetId([
            'user_name' => 'Test Admin',
            'user_email' => 'test@admin.com',
            'user_password' => Hash::make('password'),
            'user_role' => 'admin',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 2. Insertar un restaurante con zonas
        $restaurantId = DB::table('restaurants')->insertGetId([
            'restaurant_name' => 'Test Restaurant',
            'restaurant_description' => 'A test restaurant',
            'restaurant_address' => 'Test Address 123',
            'restaurant_phone' => '123456789',
            'restaurant_email' => 'test@restaurant.com',
            'restaurant_zones' => json_encode([
                ['name' => 'Main', 'capacity' => 20],
                ['name' => 'Terrace', 'capacity' => 15]
            ]),
            'restaurant_active' => true,
            'restaurant_status' => 'available',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 3. Insertar mesas para cada zona
        $tableId = DB::table('tables')->insertGetId([
            'restaurant_id' => $restaurantId,
            'table_number' => 'M1',
            'table_zone' => 'Main',
            'table_capacity' => 4,
            'table_status' => 'available',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 4. Crear una reserva
        DB::table('reservations')->insert([
            'user_id' => $userId,
            'restaurant_id' => $restaurantId,
            'table_id' => $tableId,
            'reservation_datetime' => now()->addDays(2),
            'reservation_diners' => 2,
            'reservation_status' => 'pending',
            'reservation_notes' => 'Test reservation',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 5. Verificar la inserción
        $this->command->info('Test data inserted successfully!');
        
        // Mostrar los datos insertados
        $user = DB::table('users')->where('user_id', $userId)->first();
        $restaurant = DB::table('restaurants')->where('restaurant_id', $restaurantId)->first();
        $table = DB::table('tables')->where('table_id', $tableId)->first();
        $reservation = DB::table('reservations')->where('user_id', $userId)->first();

        $this->command->info('User created: ' . json_encode($user));
        $this->command->info('Restaurant created: ' . json_encode($restaurant));
        $this->command->info('Table created: ' . json_encode($table));
        $this->command->info('Reservation created: ' . json_encode($reservation));
    }
}
