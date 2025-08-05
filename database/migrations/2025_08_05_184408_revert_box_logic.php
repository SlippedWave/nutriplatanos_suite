<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Don't drop - we need both tables for different purposes
        // BoxBalance tracks customer lending
        // BoxMovement tracks warehouse/route operations

        // Just add a comment or modify BoxMovement if needed
        Schema::table('box_movements', function (Blueprint $table) {
            // Remove sale_id since this is for warehouse operations, not sales
            if (Schema::hasColumn('box_movements', 'sale_id')) {
                $table->dropForeign(['sale_id']);
                $table->dropColumn('sale_id');
            }

            // Add route_id if not exists
            if (!Schema::hasColumn('box_movements', 'route_id')) {
                $table->foreignId('route_id')->constrained('routes')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('box_movements', function (Blueprint $table) {
            if (Schema::hasColumn('box_movements', 'route_id')) {
                $table->dropForeign(['route_id']);
                $table->dropColumn('route_id');
            }

            if (!Schema::hasColumn('box_movements', 'sale_id')) {
                $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            }
        });
    }
};
