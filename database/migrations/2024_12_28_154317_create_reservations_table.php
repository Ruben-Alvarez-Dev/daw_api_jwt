<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id('reservation_id');
            $table->foreignId('reservation_user_id')->constrained('users');
            $table->foreignId('reservation_restaurant_id')->constrained('restaurants', 'restaurant_id');
            $table->string('reservation_tables_ids'); // Array de IDs separados por comas
            $table->dateTime('reservation_datetime');
            $table->enum('reservation_status', ['pending', 'confirmed', 'seated', 'cancelled', 'no_show'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
