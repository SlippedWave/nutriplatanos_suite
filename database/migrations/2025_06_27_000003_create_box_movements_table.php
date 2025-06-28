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
        Schema::create('box_movements', function (Blueprint $table) {
            $table->id();
            $table->enum('movement_type', ['in', 'out']);
            $table->integer('quantity');
            $table->foreignId('camera_id')->nullable()->constrained('cameras')->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('route_id')->nullable()->constrained('routes')->onDelete('set null');
            $table->timestamp('moved_at');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['movement_type', 'moved_at']);
            $table->index('camera_id');
            $table->index('client_id');
            $table->index('route_id');
            $table->index('moved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('box_movements');
    }
};
