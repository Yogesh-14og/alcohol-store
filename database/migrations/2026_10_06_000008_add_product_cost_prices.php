<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'cost_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            });
        }

        if (! Schema::hasColumn('order_items', 'unit_cost')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('unit_cost', 10, 2)->nullable()->after('unit_price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('order_items', 'unit_cost')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('unit_cost');
            });
        }

        if (Schema::hasColumn('products', 'cost_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('cost_price');
            });
        }
    }
};
