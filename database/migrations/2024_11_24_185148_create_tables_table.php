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
        Schema::create('tables', function (Blueprint $table) {
            $table->id('id_table');
            $table->foreignId('id_restaurant')
                ->constrained('restaurants', 'id_restaurant')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('number');
            $table->unsignedSmallInteger('capacity')->default(4);
            $table->enum('status', [
                'available',
                'unavailable'
            ])->default('available');
            $table->timestamps();

            // Índice único compuesto
            $table->unique(['id_restaurant', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};