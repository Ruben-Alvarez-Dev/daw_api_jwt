<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * User Seeder
 * 
 * Creates initial user accounts:
 * - Admin user for system management
 * - Sample customer accounts
 * 
 * Default admin credentials:
 * - Email: admin@example.com
 * - Password: password
 * - Role: admin
 */
class UserSeeder extends Seeder
{
    /**
     * Run user seeds
     * 
     * Creates:
     * - Admin user
     * - Test customers
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'user_name' => 'Admin User',
            'user_email' => 'admin@example.com',
            'user_password' => Hash::make('password'),
            'user_role' => 'admin',
            'user_status' => 'active'
        ]);

        // Create sample customers
        User::factory(10)->create();
    }
}
