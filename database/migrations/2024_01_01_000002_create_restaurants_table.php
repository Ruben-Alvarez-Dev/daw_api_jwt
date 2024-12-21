<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restaurants Migration
 * 
 * Creates the restaurants table with essential fields:
 * - restaurant_id: Primary key
 * - restaurant_name: Public display name
 * - restaurant_business_name: Legal business name
 * - restaurant_food_type: Type of cuisine
 * - restaurant_capacity: Total seating capacity
 * - restaurant_business_email: Unique business contact
 * - restaurant_supervisor_email: Manager's contact
 * - restaurant_phone: Contact number
 * - restaurant_description: Full establishment description
 * - restaurant_pictures: Optional array of image URLs
 * - restaurant_zones: Required array of dining areas
 * - restaurant_status: Operating status
 * 
 * Note: restaurant_zones is used to validate table locations
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id('restaurant_id');
            $table->string('restaurant_name');
            $table->string('restaurant_business_name')->nullable();
            $table->string('restaurant_food_type')->nullable();
            $table->integer('restaurant_capacity');
            $table->string('restaurant_business_email')->unique()->nullable();
            $table->string('restaurant_supervisor_email')->nullable();
            $table->string('restaurant_phone')->nullable();
            $table->text('restaurant_description')->nullable();
            $table->json('restaurant_pictures')->nullable();
            $table->json('restaurant_zones');
            $table->enum('restaurant_status', [
                'active',
                'inactive',
                'tables available',
                'fully booked'
            ])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};
