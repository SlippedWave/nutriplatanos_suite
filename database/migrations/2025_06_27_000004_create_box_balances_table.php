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
        Schema::create('box_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained('customers')->onDelete('cascade');
            $table->integer('total_boxes')->default(0);
            $table->timestamps();

            // Indexes
            $table->index('total_boxes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('box_balances');
    }
};
