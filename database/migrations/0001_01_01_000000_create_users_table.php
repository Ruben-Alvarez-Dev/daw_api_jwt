<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Users Migration
 * 
 * Creates the main users table with the following fields:
 * - user_id: Primary key
 * - user_name: User's full name
 * - user_email: Unique email for authentication
 * - user_password: Hashed password
 * - user_role: User role (admin/supervisor/customer)
 * - user_phone: Optional contact number
 * - user_address: Optional delivery address
 * - user_visit_number: Counter for customer visits
 * - user_status: Account status (active/inactive)
 * - active_token: JWT token for authentication
 */
return new class extends Migration
{
   /**
    * Run the migrations.
    */
   public function up(): void
   {
       Schema::create('users', function (Blueprint $table) {
           $table->id('user_id');
           $table->string('user_name');
           $table->string('user_email')->unique();
           $table->timestamp('user_email_verified_at')->nullable();
           $table->string('user_password');
           $table->enum('user_role', ['admin', 'supervisor', 'customer'])->default('customer'); // Añadido el role
           $table->string('user_created_by')->nullable(); // Email del supervisor que creó al usuario
           $table->string('user_phone')->nullable();
           $table->text('user_address')->nullable();
           $table->integer('user_visit_number')->default(0);
           $table->enum('user_status', ['active', 'inactive'])->default('active');
           $table->text('active_token')->nullable(); // Para JWT
           $table->rememberToken();
           $table->timestamps();
       });

       Schema::create('password_reset_tokens', function (Blueprint $table) {
           $table->string('email')->primary();
           $table->string('token');
           $table->timestamp('created_at')->nullable();
       });

       Schema::create('sessions', function (Blueprint $table) {
           $table->string('id')->primary();
           $table->foreignId('user_id')->nullable()->index();
           $table->string('ip_address', 45)->nullable();
           $table->text('user_agent')->nullable();
           $table->longText('payload');
           $table->integer('last_activity')->index();
       });
   }

   /**
    * Reverse the migrations.
    */
   public function down(): void
   {
       Schema::dropIfExists('sessions');
       Schema::dropIfExists('password_reset_tokens');
       Schema::dropIfExists('users');
   }
};