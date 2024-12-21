<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reservations Migration
 * 
 * Creates the reservations management table:
 * - reservation_id: Primary key
 * - reservation_user_id: Customer making reservation
 * - reservation_restaurant_id: Target restaurant
 * - reservation_table_ids: Array of reserved table IDs
 * - reservation_date_time: Scheduled date and time
 * - reservation_guests: Party size
 * - reservation_comment: Optional special requests
 * - reservation_status: Booking status
 * 
 * Constraints:
 * - Cascade delete with both user and restaurant
 * - reservation_table_ids must contain valid table IDs
 * - reservation_guests must not exceed total table capacity
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id('reservation_id');
            $table->foreignId('reservation_user_id')
                ->constrained('users', 'user_id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('reservation_restaurant_id')
                ->constrained('restaurants', 'restaurant_id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->json('reservation_table_ids');
            $table->dateTime('reservation_date_time');
            $table->integer('reservation_guests');
            $table->text('reservation_comment')->nullable();
            $table->enum('reservation_status', [
                'pending',
                'confirmed',
                'seated',
                'completed',
                'canceled'
            ])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
