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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('carrier_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['date', 'carrier_id']);
            $table->index('closed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
