<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id('id_restaurant');
            $table->string('name');
            $table->json('zones')->nullable();  // Sin default aquí
            $table->unsignedSmallInteger('capacity')->nullable()->default(50);
            $table->boolean('isActive')->default(true);
            $table->enum('status', [
                'tables available',
                'fully booked'
            ])->default('tables available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurants');
    }
};