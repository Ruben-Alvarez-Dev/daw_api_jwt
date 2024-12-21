<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id('restaurant_id');
            $table->string('restaurant_name');
            $table->string('restaurant_business_name');
            $table->string('restaurant_food_type');
            $table->integer('restaurant_capacity');
            $table->string('restaurant_business_email')->unique();
            $table->string('restaurant_supervisor_email');
            $table->string('restaurant_phone');
            $table->text('restaurant_description');
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
