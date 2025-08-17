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
            $table->foreignId('camera_id')->nullable()->constrained('cameras')->onDelete('set null');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->enum('movement_type', ['warehouse_to_route', 'route_to_warehouse', 'route_to_route', 'truck_inventory']);
            $table->enum('box_content_status', ['empty', 'full'])
                ->default('empty')
                ->comment('Indicates whether the box is empty or contains product when moved');
            $table->integer('quantity');
            $table->timestamp('moved_at');
            $table->softDeletes();
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
