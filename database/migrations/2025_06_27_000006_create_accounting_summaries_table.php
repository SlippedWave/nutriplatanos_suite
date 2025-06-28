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
        Schema::create('accounting_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly']);
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('total_expenses', 12, 2)->default(0);
            $table->decimal('net_total', 12, 2)->default(0);
            $table->timestamps();

            // Indexes
            $table->unique(['date', 'period_type']);
            $table->index('period_type');
            $table->index('net_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_summaries');
    }
};
