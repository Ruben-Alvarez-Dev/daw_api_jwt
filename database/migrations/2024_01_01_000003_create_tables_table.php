<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restaurant Tables Migration
 * 
 * Creates the tables table for restaurant seating:
 * - table_id: Primary key
 * - restaurant_id: Foreign key to restaurants
 * - table_name: Unique identifier within restaurant
 * - table_capacity: Number of seats
 * - table_zone: Must match a zone from restaurant_zones
 * - table_status: Current availability status
 * 
 * Constraints:
 * - Composite unique key [restaurant_id, table_name]
 * - Cascade delete with restaurant
 * - table_zone must match parent restaurant zones
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id('table_id');
            $table->foreignId('restaurant_id')
                ->constrained('restaurants', 'restaurant_id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('table_name');
            $table->integer('table_capacity');
            $table->string('table_zone');
            $table->enum('table_status', [
                'available',
                'reserved',
                'occupied',
                'maintenance'
            ])->default('available');
            $table->timestamps();

            // Índice único para evitar nombres duplicados en el mismo restaurante
            $table->unique(['restaurant_id', 'table_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
