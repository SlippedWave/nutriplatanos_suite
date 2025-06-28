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
        Schema::table('sales', function (Blueprint $table) {
            // Add the new required columns
            $table->foreignId('route_id')->nullable()->after('user_id')->constrained('routes')->onDelete('set null');
            $table->decimal('weight_kg', 8, 3)->after('route_id');
            $table->decimal('price_per_kg', 8, 2)->after('weight_kg');

            // Rename status to payment_status and update possible values
            $table->renameColumn('status', 'payment_status');

            // Update total_amount to ensure it matches our requirements
            $table->decimal('total_amount', 10, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['route_id']);
            $table->dropColumn(['route_id', 'weight_kg', 'price_per_kg']);
            $table->renameColumn('payment_status', 'status');
        });
    }
};
