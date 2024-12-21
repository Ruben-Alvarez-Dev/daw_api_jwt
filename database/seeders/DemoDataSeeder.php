<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        // Limpiar tablas existentes
        DB::table('reservations')->delete();
        DB::table('tables')->delete();
        DB::table('restaurants')->delete();
        DB::table('users')->delete();

        // 1. Usuarios base (admin, supervisor, customer)
        $users = [
            [
                'user_name' => 'Admin',
                'user_email' => 'admin@admin.com',
                'user_password' => Hash::make('admin'),
                'user_role' => 'admin'
            ],
            [
                'user_name' => 'Supervisor',
                'user_email' => 'supervisor@supervisor.com',
                'user_password' => Hash::make('supervisor'),
                'user_role' => 'supervisor'
            ],
            [
                'user_name' => 'Customer',
                'user_email' => 'customer@customer.com',
                'user_password' => Hash::make('customer'),
                'user_role' => 'customer'
            ],
            [
                'user_name' => 'María García',
                'user_email' => 'maria.garcia@gmail.com',
                'user_password' => Hash::make('password123'),
                'user_role' => 'customer'
            ],
            [
                'user_name' => 'Juan Martínez',
                'user_email' => 'juan.martinez@hotmail.com',
                'user_password' => Hash::make('password123'),
                'user_role' => 'customer'
            ],
            [
                'user_name' => 'Ana López',
                'user_email' => 'ana.lopez@yahoo.com',
                'user_password' => Hash::make('password123'),
                'user_role' => 'customer'
            ],
            [
                'user_name' => 'Carlos Rodríguez',
                'user_email' => 'carlos.rodriguez@outlook.com',
                'user_password' => Hash::make('password123'),
                'user_role' => 'customer'
            ],
            [
                'user_name' => 'Laura Fernández',
                'user_email' => 'laura.fernandez@gmail.com',
                'user_password' => Hash::make('password123'),
                'user_role' => 'customer'
            ],
            [
                'user_name' => 'David Sánchez',
                'user_email' => 'david.sanchez@yahoo.com',
                'user_password' => Hash::make('password123'),
                'user_role' => 'customer'
            ],
            [
                'user_name' => 'Elena Pérez',
                'user_email' => 'elena.perez@gmail.com',
                'user_password' => Hash::make('password123'),
                'user_role' => 'customer'
            ]
        ];

        foreach ($users as $user) {
            $user['created_at'] = now();
            $user['updated_at'] = now();
            DB::table('users')->insert($user);
        }

        // 2. Restaurantes con sus zonas
        $restaurants = [
            [
                'restaurant_name' => 'La Parrilla Mediterránea',
                'restaurant_description' => 'Restaurante especializado en carnes a la brasa y pescados frescos',
                'restaurant_address' => 'Calle Mayor 123, Valencia',
                'restaurant_phone' => '961234567',
                'restaurant_email' => 'info@parrillamediterranea.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 30],
                    ['name' => 'Terraza', 'capacity' => 20],
                    ['name' => 'Privado', 'capacity' => 12]
                ])
            ],
            [
                'restaurant_name' => 'El Rincón del Mar',
                'restaurant_description' => 'Marisquería y arroces con las mejores vistas al mar',
                'restaurant_address' => 'Paseo Marítimo 45, Valencia',
                'restaurant_phone' => '962345678',
                'restaurant_email' => 'reservas@rincondelmar.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 40],
                    ['name' => 'Terraza', 'capacity' => 30]
                ])
            ],
            [
                'restaurant_name' => 'Pasta & Love',
                'restaurant_description' => 'Auténtica cocina italiana con pasta fresca casera',
                'restaurant_address' => 'Calle Colón 67, Valencia',
                'restaurant_phone' => '963456789',
                'restaurant_email' => 'info@pastaandlove.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 35],
                    ['name' => 'Cantina', 'capacity' => 15],
                    ['name' => 'Giardino', 'capacity' => 25]
                ])
            ],
            [
                'restaurant_name' => 'El Huerto Vegano',
                'restaurant_description' => 'Cocina vegetariana y vegana creativa',
                'restaurant_address' => 'Calle Ruzafa 89, Valencia',
                'restaurant_phone' => '964567890',
                'restaurant_email' => 'reservas@huertovegano.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 30],
                    ['name' => 'Jardín', 'capacity' => 20]
                ])
            ],
            [
                'restaurant_name' => 'Sushi Fusion',
                'restaurant_description' => 'Fusión de cocina japonesa tradicional y moderna',
                'restaurant_address' => 'Avenida del Puerto 234, Valencia',
                'restaurant_phone' => '965678901',
                'restaurant_email' => 'info@sushifusion.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 40],
                    ['name' => 'Tatami', 'capacity' => 16],
                    ['name' => 'Lounge', 'capacity' => 20]
                ])
            ],
            [
                'restaurant_name' => 'Tapas & Vinos',
                'restaurant_description' => 'Bar de tapas tradicionales y vinos selectos',
                'restaurant_address' => 'Plaza del Ayuntamiento 12, Valencia',
                'restaurant_phone' => '966789012',
                'restaurant_email' => 'reservas@tapasyvinos.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 35],
                    ['name' => 'Bodega', 'capacity' => 15]
                ])
            ],
            [
                'restaurant_name' => 'La Brasería',
                'restaurant_description' => 'Carnes premium y platos a la brasa',
                'restaurant_address' => 'Calle Sagunto 45, Valencia',
                'restaurant_phone' => '967890123',
                'restaurant_email' => 'info@labraseria.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 45],
                    ['name' => 'Terraza', 'capacity' => 25]
                ])
            ],
            [
                'restaurant_name' => 'Mar y Montaña',
                'restaurant_description' => 'Lo mejor del mar y la montaña en un solo lugar',
                'restaurant_address' => 'Avenida del Cid 78, Valencia',
                'restaurant_phone' => '968901234',
                'restaurant_email' => 'reservas@marymontana.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 40],
                    ['name' => 'Mirador', 'capacity' => 20]
                ])
            ],
            [
                'restaurant_name' => 'El Asador',
                'restaurant_description' => 'Asador tradicional con horno de leña',
                'restaurant_address' => 'Calle San Vicente 90, Valencia',
                'restaurant_phone' => '969012345',
                'restaurant_email' => 'info@elasador.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 35],
                    ['name' => 'Patio', 'capacity' => 25]
                ])
            ],
            [
                'restaurant_name' => 'La Terraza Garden',
                'restaurant_description' => 'Cocina mediterránea en un entorno ajardinado',
                'restaurant_address' => 'Avenida de Francia 123, Valencia',
                'restaurant_phone' => '960123456',
                'restaurant_email' => 'reservas@terrazagarden.com',
                'restaurant_zones' => json_encode([
                    ['name' => 'Main', 'capacity' => 30],
                    ['name' => 'Jardín', 'capacity' => 35],
                    ['name' => 'Chill Out', 'capacity' => 15]
                ])
            ]
        ];

        $restaurantIds = [];
        foreach ($restaurants as $restaurant) {
            $restaurant['restaurant_active'] = true;
            $restaurant['restaurant_status'] = 'available';
            $restaurant['created_at'] = now();
            $restaurant['updated_at'] = now();
            $restaurantIds[] = DB::table('restaurants')->insertGetId($restaurant);
        }

        // 3. Mesas para cada restaurante
        foreach ($restaurantIds as $restaurantId) {
            $restaurant = DB::table('restaurants')->where('restaurant_id', $restaurantId)->first();
            $zones = json_decode($restaurant->restaurant_zones, true);
            
            foreach ($zones as $zone) {
                // 5 mesas por zona
                for ($i = 1; $i <= 5; $i++) {
                    DB::table('tables')->insert([
                        'restaurant_id' => $restaurantId,
                        'table_number' => $zone['name'] . '-' . $i,
                        'table_zone' => $zone['name'],
                        'table_capacity' => $i <= 2 ? 2 : ($i <= 4 ? 4 : 6),
                        'table_status' => 'available',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }

        // 4. Reservas (40 en total)
        $tables = DB::table('tables')->get();
        $customerIds = DB::table('users')->where('user_role', 'customer')->pluck('user_id');
        
        for ($i = 0; $i < 40; $i++) {
            $table = $tables->random();
            $customerId = $customerIds->random();
            
            DB::table('reservations')->insert([
                'user_id' => $customerId,
                'restaurant_id' => $table->restaurant_id,
                'table_id' => $table->table_id,
                'reservation_datetime' => now()->addDays(rand(1, 30))->setHour(rand(12, 22))->setMinute(0)->setSecond(0),
                'reservation_diners' => min($table->table_capacity, rand(1, 6)),
                'reservation_status' => 'pending',
                'reservation_notes' => 'Reserva automática de prueba',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->command->info('Demo data inserted successfully!');
    }
}
