<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Personal Access Tokens Migration
 * 
 * Creates the personal_access_tokens table for API authentication:
 * - tokenable: Polymorphic relation to user
 * - name: Token name/purpose
 * - token: Hashed token string
 * - abilities: Token permissions
 * - last_used_at: Last usage timestamp
 * - expires_at: Token expiration
 * 
 * Used by Laravel Sanctum for API token authentication
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
