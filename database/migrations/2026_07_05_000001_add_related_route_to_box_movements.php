<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('box_movements', function (Blueprint $table) {
            // Counterpart route for a route-to-route transfer (the "other" route).
            $table->foreignId('related_route_id')
                ->nullable()
                ->after('route_id')
                ->constrained('routes')
                ->nullOnDelete();

            // Direction of a route-to-route transfer, relative to the owning route (route_id):
            // 'out' = this route sends boxes to the counterpart, 'in' = this route receives from it.
            $table->string('transfer_direction')
                ->nullable()
                ->after('related_route_id')
                ->comment('out|in — only set for route_to_route movements');
        });
    }

    public function down(): void
    {
        Schema::table('box_movements', function (Blueprint $table) {
            if (Schema::hasColumn('box_movements', 'related_route_id')) {
                $table->dropForeign(['related_route_id']);
                $table->dropColumn('related_route_id');
            }

            if (Schema::hasColumn('box_movements', 'transfer_direction')) {
                $table->dropColumn('transfer_direction');
            }
        });
    }
};
