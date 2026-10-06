<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $missingColumns = [];

        foreach ([
            'user_id',
            'order_number',
            'total_amount',
            'status',
            'payment_status',
            'payment_method',
            'shipping_name',
            'shipping_phone',
            'shipping_address',
        ] as $column) {
            if (! Schema::hasColumn('orders', $column)) {
                $missingColumns[] = $column;
            }
        }

        if ($missingColumns === []) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) use ($missingColumns): void {
            foreach ($missingColumns as $column) {
                match ($column) {
                    'user_id' => $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(),
                    'order_number' => $table->string('order_number', 64)->nullable()->unique(),
                    'total_amount' => $table->decimal('total_amount', 10, 2)->default(0),
                    'status' => $table->string('status')->default('pending'),
                    'payment_status' => $table->string('payment_status')->default('unpaid'),
                    'payment_method' => $table->string('payment_method')->default('cod'),
                    'shipping_name' => $table->string('shipping_name')->nullable(),
                    'shipping_phone' => $table->string('shipping_phone')->nullable(),
                    'shipping_address' => $table->text('shipping_address')->nullable(),
                };
            }
        });
    }

    public function down(): void
    {
        // Intentionally retain repaired columns so rollback cannot remove live order data.
    }
};
