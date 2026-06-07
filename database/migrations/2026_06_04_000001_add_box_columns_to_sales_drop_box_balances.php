<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->integer('boxes_delivered')->default(0)->after('total_amount');
            $table->integer('boxes_returned')->default(0)->after('boxes_delivered');
        });

        Schema::dropIfExists('box_balances');
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['boxes_delivered', 'boxes_returned']);
        });

        Schema::create('box_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->integer('delivered_boxes')->default(0);
            $table->integer('returned_boxes')->default(0);
        });
    }
};
