<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $missingOrderColumns = [];

        foreach (['payment_method', 'shipping_name', 'shipping_phone', 'shipping_address'] as $column) {
            if (! Schema::hasColumn('orders', $column)) {
                $missingOrderColumns[] = $column;
            }
        }

        if ($missingOrderColumns !== []) {
            Schema::table('orders', function (Blueprint $table) use ($missingOrderColumns) {
                foreach ($missingOrderColumns as $column) {
                    match ($column) {
                        'payment_method' => $table->string('payment_method')->default('cod'),
                        'shipping_name' => $table->string('shipping_name')->nullable(),
                        'shipping_phone' => $table->string('shipping_phone')->nullable(),
                        'shipping_address' => $table->text('shipping_address')->nullable(),
                    };
                }
            });
        }

        if (! Schema::hasTable('order_items')) {
            Schema::create('order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
                $table->string('product_name');
                $table->decimal('unit_price', 10, 2);
                $table->unsignedInteger('quantity');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'shipping_name',
                'shipping_phone',
                'shipping_address',
            ]);
        });
    }
};
