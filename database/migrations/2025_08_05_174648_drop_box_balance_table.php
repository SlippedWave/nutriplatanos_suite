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
        //
        // This migration is intentionally empty as it is used to drop the box_balance table.
        // The table is no longer needed as the box balance logic has been moved to the Customer model.
        //
        Schema::dropIfExists('box_balance');
        // If you need to perform any additional cleanup or modifications, you can do so here.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('box_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->integer('delivered_boxes')->default(0);
            $table->integer('returned_boxes')->default(0);
        });
    }
};
