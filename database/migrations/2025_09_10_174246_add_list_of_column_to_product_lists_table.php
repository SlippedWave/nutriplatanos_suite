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
        Schema::table('product_lists', function (Blueprint $table) {
            //
            $table->renameColumn('sale_id', 'listable_id');
            $table->morphs('listable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_lists', function (Blueprint $table) {
            //
            $table->renameColumn('listable_id', 'sale_id');
            $table->dropMorphs('listable_type');
        });
    }
};
