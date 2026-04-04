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
            // Rename 'net_amount_due' to 'total_amount_excluding_refunds' for clarity
            $table->renameColumn('net_amount_due', 'total_amount_excluding_refunds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('total_amount_excluding_refunds', 'net_amount_due');
        });
    }
};
