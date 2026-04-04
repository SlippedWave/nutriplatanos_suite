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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('curp');
            $table->string('rfc');
            $table->string('email')->unique();
            $table->enum('role', ['admin', 'coordinator', 'carrier'])->default('carrier')
                ->comment('Role of the user, e.g., admin, coordinator, carrier');
            $table->boolean('active')->default(true)
                ->comment('Indicates if the user account is active or not');
            $table->string('password');
            $table->string('address');
            $table->string('emergency_contact');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relationship');
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable()
                ->comment('Timestamp of the last login of the user');
            $table->string('last_login_ip', 45)->nullable()
                ->comment('IP address of the last login of the user');
            $table->timestamps();
            $table->softDeletes();
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
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
