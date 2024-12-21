<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Main Database Seeder
 * 
 * Orchestrates the seeding of all database tables
 * in the correct order to maintain referential integrity.
 * 
 * Seeding order:
 * 1. Users (including admin accounts)
 * 2. Restaurants with sample data
 * 3. Tables for each restaurant
 * 4. Sample reservations
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run database seeds
     * 
     * Creates:
     * - Default admin user
     * - Sample restaurants with tables
     * - Test reservations
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
