<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('route_id')->constrained()->onDelete('cascade');
            $table->enum('payment_status', [
                'pending',
                'paid',
                'partial',
                'cancelled'
            ])->default('pending');
            $table->timestamps();
            $table->softDeletes(); // For soft delete functionality
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
