<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
