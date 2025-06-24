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
        Schema::table('customers', function (Blueprint $table) {
            $table->integer('box_balance')->default(0)->after('rfc')
                ->comment('Balance of boxes for the customer, used for tracking box inventory');
            $table->boolean('is_active')->default(true)->after('box_balance')
                ->comment('Indicates if the customer is active or not, useful for filtering active customers
                and managing customer relationships');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
