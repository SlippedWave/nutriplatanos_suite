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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('carrier')->after('email')
                ->comment('Role of the user, e.g., admin, user, etc.');
            $table->boolean('is_active')->default(true)->after('role')
                ->comment('Indicates if the user account is active or not');
            $table->timestamp('last_modified_at')->nullable()->after('is_active')
                ->comment('Timestamp of the last modification made to the user account');
            $table->integer('last_modified_by')->nullable()->after('last_modified_at')
                ->comment('ID of the user who last modified this account');
            $table->timestamp('last_login_at')->nullable()->after('is_active')
                ->comment('Timestamp of the last login of the user');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at')
                ->comment('IP address of the last login of the user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
