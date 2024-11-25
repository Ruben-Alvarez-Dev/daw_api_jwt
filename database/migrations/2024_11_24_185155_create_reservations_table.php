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
            $table->id('id_reservation');
            $table->foreignId('id_user')
                ->constrained('users', 'id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('id_restaurant')
                ->constrained('restaurants', 'id_restaurant')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->json('tables')->nullable(); // Array de números de mesa
            $table->dateTime('datetime');
            $table->enum('status', [
                'pending',
                'confirmed',
                'seated',
                'canceled',
                'closed'
            ])->default('pending');
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